<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use Closure;
use Generator;
use IteratorAggregate;
use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Asserts\ToBeAbstract;
use StructuraPhp\Structura\Asserts\ToBeAnonymousClasses;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\Asserts\ToBeEnums;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Asserts\ToBeInterfaces;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Asserts\ToBeTraits;
use StructuraPhp\Structura\Asserts\ToExtend;
use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Asserts\ToHaveAttribute;
use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToHaveSuffix;
use StructuraPhp\Structura\Asserts\ToImplement;
use StructuraPhp\Structura\Asserts\ToImplementNothing;
use StructuraPhp\Structura\Asserts\ToNotDependsOn;
use StructuraPhp\Structura\Asserts\ToNotUseTrait;
use StructuraPhp\Structura\Asserts\ToOnlyImplement;
use StructuraPhp\Structura\Asserts\ToOnlyUseTrait;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Asserts\ToUseTrait;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ExprType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Traversable;

/**
 * @implements IteratorAggregate<int,ExprInterface|Expr>
 */
class Expr implements IteratorAggregate
{
    /** @var array<int,Expr|ExprInterface> */
    private array $asserts = [];

    /** @var array<int,array<int,class-string>> */
    private array $hiddenDependencies = [];

    public function __construct(
        private readonly ExprType $exprType = ExprType::And,
        private readonly int $depth = 0,
    ) {}

    public function __toString(): string
    {
        $separator = PHP_EOL . str_repeat(' ', $this->depth * 2);

        return $this->exprType === ExprType::And
            ? implode($separator . ' & ', $this->asserts)
            : implode($separator . ' | ', $this->asserts);
    }

    public function getExprType(): ExprType
    {
        return $this->exprType;
    }

    public function or(Closure $closure): self
    {
        $assertion = new self(ExprType::Or, $this->depth + 1);
        $closure($assertion);
        $this->asserts[] = $assertion;

        return $this;
    }

    public function and(Closure $closure): self
    {
        $expr = new self(ExprType::And, $this->depth + 1);
        $closure($expr);
        $this->asserts[] = $expr;

        return $this;
    }

    public function toBeAbstract(string $message = ''): self
    {
        return $this->addExpr(new ToBeAbstract($message));
    }

    public function addExpr(ExprInterface $expr): self
    {
        $this->asserts[] = $expr;

        return $this;
    }

    public function toBeClasses(string $message = ''): self
    {
        return $this->addExpr(new ToBeClasses($message));
    }

    public function toBeAnonymousClasses(string $message = ''): self
    {
        return $this->addExpr(new ToBeAnonymousClasses($message));
    }

    public function toBeEnums(string $message = ''): self
    {
        return $this->addExpr(new ToBeEnums($message));
    }

    public function toBeInterfaces(string $message = ''): self
    {
        return $this->addExpr(new ToBeInterfaces($message));
    }

    public function toBeInvokable(string $message = ''): self
    {
        return $this->toHaveMethod('__invoke', $message);
    }

    public function toHaveMethod(string $name, string $message = ''): self
    {
        return $this->addExpr(new ToHaveMethod($name, $message));
    }

    public function toBeTraits(string $message = ''): self
    {
        return $this->addExpr(new ToBeTraits($message));
    }

    public function toBeFinal(string $message = ''): self
    {
        return $this->addExpr(new ToBeFinal($message));
    }

    public function toBeReadonly(string $message = ''): self
    {
        return $this->addExpr(new ToBeReadonly($message));
    }

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function dependsOnlyOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOn(
                array_unique(array_merge((array) $names, ...$this->hiddenDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toNotDependsOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToNotDependsOn((array) $names, (array) $patterns, $message),
        );
    }

    /**
     * @param class-string $name
     */
    public function toExtend(string $name, string $message = ''): self
    {
        $this->hiddenDependencies[] = [$name];

        return $this->addExpr(new ToExtend($name, $message));
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toImplement(array|string $names, string $message = ''): self
    {
        $this->hiddenDependencies[] = (array) $names;

        return $this->addExpr(new ToImplement($names, $message));
    }

    public function toImplementNothing(string $message = ''): self
    {
        return $this->addExpr(new ToImplementNothing($message));
    }

    /**
     * @param class-string $name
     */
    public function toHaveAttribute(string $name, string $message = ''): self
    {
        $this->hiddenDependencies[] = [$name];

        return $this->addExpr(new ToHaveAttribute($name, $message));
    }

    public function toHavePrefix(string $prefix): self
    {
        return $this->addExpr(new ToHavePrefix($prefix));
    }

    public function toHaveSuffix(string $suffix, string $message = ''): self
    {
        return $this->addExpr(new ToHaveSuffix($suffix, $message));
    }

    /**
     * @param class-string $name
     */
    public function toOnlyImplement(string $name, string $message = ''): self
    {
        $this->hiddenDependencies[] = [$name];

        return $this->addExpr(new ToOnlyImplement($name, $message));
    }

    /**
     * @param class-string $name
     */
    public function toOnlyUseTrait(string $name, string $message = ''): self
    {
        $this->hiddenDependencies[] = [$name];

        return $this->addExpr(new ToOnlyUseTrait($name, $message));
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toUseTrait(array|string $names, string $message = ''): self
    {
        $this->hiddenDependencies[] = (array) $names;

        return $this->addExpr(new ToUseTrait($names, $message));
    }

    public function toNotUseTrait(string $message = ''): self
    {
        return $this->addExpr(new ToNotUseTrait($message));
    }

    public function toUseStrictTypes(string $message = ''): self
    {
        return $this->toUseDeclare('strict_types', '1', $message);
    }

    public function toUseDeclare(string $key, string $value, string $message = ''): self
    {
        return $this->addExpr(new ToUseDeclare($key, $value, $message));
    }

    public function toExtendsNothing(string $message = ''): self
    {
        return $this->addExpr(new ToExtendNothing($message));
    }

    public function toHaveConstructor(string $message = ''): self
    {
        return $this->toHaveMethod('__construct', $message);
    }

    public function toHaveDestructor(string $message = ''): self
    {
        return $this->toHaveMethod('__destruct', $message);
    }

    /**
     * @return Generator<Expr|ExprInterface>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->asserts as $assert) {
            yield $assert;
        }
    }

    /**
     * @return array<int,ViolationValueObject>
     */
    public function getViolations(ClassDescription $class): array
    {
        $return = [];
        foreach ($this->asserts as $assert) {
            if ($assert instanceof ExprInterface) {
                $return[] = $assert->getViolation($class);
            } elseif ($assert instanceof Expr) {
                $return = [
                    ...$return,
                    ...array_values($assert->getViolations($class)),
                ];
            }
        }

        return $return;
    }
}
