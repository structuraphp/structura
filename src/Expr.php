<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use Closure;
use Generator;
use IteratorAggregate;
use StructuraPhp\Structura\Concerns\Expr\DependencyAssert;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Concerns\Expr\NameAssert;
use StructuraPhp\Structura\Concerns\Expr\OtherAssert;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Contracts\Expr\DependencyAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\MethodAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\NameAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\OtherAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\RelationAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\TypeAssertInterface;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ExprType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Traversable;

/**
 * @implements IteratorAggregate<int,ExprInterface|Expr>
 */
class Expr implements IteratorAggregate, TypeAssertInterface, DependencyAssertInterface, RelationAssertInterface, MethodAssertInterface, NameAssertInterface, OtherAssertInterface
{
    use TypeAssert;
    use DependencyAssert;
    use RelationAssert;
    use MethodAssert;
    use NameAssert;
    use OtherAssert;

    /** @var array<int,Expr|ExprInterface> */
    private array $asserts = [];

    /** @var array<int,array<int,class-string>> */
    private array $attributDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $extendDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $implementDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $traitDependencies = [];

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

    public function addExpr(ExprInterface $expr): self
    {
        $this->asserts[] = $expr;

        return $this;
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
