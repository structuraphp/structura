<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Contracts\ShouldInterface;
use StructuraPhp\Structura\Except;

/**
 * @template T of AbstractExpr
 *
 * @implements ShouldInterface<T>
 */
class ThatBuilder implements ShouldInterface
{
    /**
     * @param class-string<T> $abstractExpr
     */
    public function __construct(
        protected readonly RuleBuilder $ruleBuilder,
        protected readonly string $abstractExpr,
    ) {}

    public function should(Closure $closure): RuleBuilder
    {
        /** @var T $expression */
        $expression = new $this->abstractExpr();
        $closure($expression);

        $this->ruleBuilder->setShould($expression);

        return $this->ruleBuilder;
    }

    public function except(Closure $closure): ShouldInterface
    {
        $exception = new Except();
        $closure($exception);

        $this->ruleBuilder->setExpect($exception);

        return $this;
    }
}
