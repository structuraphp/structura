<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use Closure;
use StructuraPhp\Structura\Contracts\ShouldInterface;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;

class ThatBuilder implements ShouldInterface
{
    public function __construct(protected readonly RuleBuilder $ruleBuilder) {}

    public function should(Closure $closure): RuleBuilder
    {
        $expression = new Expr();
        $closure($expression);

        $this->ruleBuilder->addShould($expression);

        return $this->ruleBuilder;
    }

    public function except(Closure $closure): ShouldInterface
    {
        $exception = new Except();
        $closure($exception);

        $this->ruleBuilder->addExpect($exception);

        return $this;
    }
}
