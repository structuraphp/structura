<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use Closure;
use InvalidArgumentException;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprIteratorAggregate;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\ExprType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Traversable;

/**
 * @implements ExprIteratorAggregate<AbstractExpr|ExprInterface>
 */
class AbstractExpr implements ExprIteratorAggregate
{
    /** @var array<int,AbstractExpr|ExprInterface> */
    private array $asserts = [];

    final public function __construct(
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

    public function or(Closure $closure): static
    {
        $assertion = new static(ExprType::Or, $this->depth + 1);
        $closure($assertion);
        $this->asserts[] = $assertion;

        return $this;
    }

    public function and(Closure $closure): static
    {
        $expr = new static(ExprType::And, $this->depth + 1);
        $closure($expr);
        $this->asserts[] = $expr;

        return $this;
    }

    public function addExpr(ExprInterface $expr): static
    {
        $this->asserts[] = $expr;

        return $this;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->asserts as $assert) {
            yield $assert;
        }
    }

    /**
     * @return array<int,ViolationValueObject>
     */
    public function getViolations(ClassDescription|ScriptDescription $description): array
    {
        $return = [];

        /** @var AbstractExpr|ExprInterface $assert */
        foreach ($this->asserts as $assert) {
            if ($assert instanceof ExprScriptInterface) {
                $return[] = $assert->getViolation($description);

                continue;
            }

            if ($assert instanceof ExprInterface && $description instanceof ClassDescription) {
                $return[] = $assert->getViolation($description);

                continue;
            }

            if ($assert instanceof AbstractExpr) {
                $return = [
                    ...$return,
                    ...array_values($assert->getViolations($description)),
                ];

                continue;
            }

            throw new InvalidArgumentException();
        }

        return $return;
    }
}
