<?php

declare(strict_types=1);

namespace Structura;

use Closure;
use Generator;
use IteratorAggregate;
use Structura\Asserts\ToBeAbstract;
use Structura\Asserts\ToBeAnonymousClasses;
use Structura\Asserts\ToBeClasses;
use Structura\Asserts\ToBeEnums;
use Structura\Asserts\ToBeFinal;
use Structura\Asserts\ToBeInterfaces;
use Structura\Asserts\ToBeReadonly;
use Structura\Asserts\ToBeTraits;
use Structura\Asserts\ToDependsOn;
use Structura\Asserts\ToExtend;
use Structura\Asserts\ToExtendNothing;
use Structura\Asserts\ToHaveAttribute;
use Structura\Asserts\ToHaveMethod;
use Structura\Asserts\ToHavePrefix;
use Structura\Asserts\ToHaveSuffix;
use Structura\Asserts\ToImplement;
use Structura\Asserts\ToImplementNothing;
use Structura\Asserts\ToNotDependsOn;
use Structura\Asserts\ToOnlyImplement;
use Structura\Asserts\ToOnlyUse;
use Structura\Asserts\ToUse;
use Structura\Asserts\ToUseDeclare;
use Structura\Asserts\ToUseNothing;
use Structura\Contracts\ExprInterface;
use Structura\Enums\ExprType;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ExpectValueObject;
use Structura\ValueObjects\ViolationValueObject;
use Traversable;

/**
 * @implements IteratorAggregate<int,ExprInterface|Expr>
 */
class Expr implements IteratorAggregate
{
    /** @var array<int,ExprInterface|Expr> */
    private array $asserts;

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
     */
    public function toDependsOn(string|array $names): self
    {
        return $this->addExpr(new ToDependsOn((array) $names));
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toNotDependsOn(string|array $names): self
    {
        return $this->addExpr(new ToNotDependsOn((array) $names));
    }

    /**
     * @param class-string $name
     */
    public function toExtend(string $name, string $message = ''): self
    {
        return $this->addExpr(new ToExtend($name, $message));
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toImplement(array|string $names, string $message = ''): self
    {
        return $this->addExpr(new ToImplement($names, $message));
    }

    public function toImplementNothing(string $message = ''): self
    {
        return $this->addExpr(new ToImplementNothing($message));
    }

    public function toHaveAttribute(string $name, string $message = ''): self
    {
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
        return $this->addExpr(new ToOnlyImplement($name, $message));
    }

    /**
     * @param class-string $name
     */
    public function toOnlyUse(string $name, string $message = ''): self
    {
        return $this->addExpr(new ToOnlyUse($name, $message));
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toUse(array|string $names, string $message = ''): self
    {
        return $this->addExpr(new ToUse($names, $message));
    }

    public function toUseNothing(?ExpectValueObject $expect = null, string $message = ''): self
    {
        return $this->addExpr(new ToUseNothing($message, $expect));
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
     * @return Generator<ExprInterface|Expr>
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
