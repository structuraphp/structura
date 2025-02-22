<?php

declare(strict_types=1);

namespace Structura\Builder;

use Closure;
use Structura\Contracts\ShouldInterface;
use Structura\Contracts\ThatInterface;
use Structura\Expr;

class FinderBuilder extends ThatBuilder implements ThatInterface
{
    public function that(Closure $closure): ShouldInterface
    {
        $expression = new Expr();
        $closure($expression);

        $this->ruleBuilder->addThat($expression);

        return new ThatBuilder($this->ruleBuilder);
    }
}
