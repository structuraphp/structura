<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use Closure;
use StructuraPhp\Structura\Contracts\ShouldInterface;
use StructuraPhp\Structura\Contracts\ThatInterface;
use StructuraPhp\Structura\Expr;

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
