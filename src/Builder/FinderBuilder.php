<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Contracts\ShouldInterface;
use StructuraPhp\Structura\Contracts\ThatInterface;

/**
 * @template T of AbstractExpr
 *
 * @extends ThatBuilder<T>
 *
 * @implements ThatInterface<T>
 */
class FinderBuilder extends ThatBuilder implements ThatInterface
{
    public function that(Closure $closure): ShouldInterface
    {
        /** @var T $expression */
        $expression = new $this->abstractExpr();
        $closure($expression);

        $this->ruleBuilder->addThat($expression);

        return new ThatBuilder($this->ruleBuilder, $this->abstractExpr);
    }
}
