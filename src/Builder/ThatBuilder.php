<?php

declare(strict_types=1);

namespace Structura\Builder;

use Closure;
use Structura\Contracts\ShouldInterface;
use Structura\Except;
use Structura\Expr;

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
