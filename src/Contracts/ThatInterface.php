<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use StructuraPhp\Structura\Expr;

interface ThatInterface extends ShouldInterface
{
    /**
     * @param Closure(Expr $expr): Expr|Closure(Expr $expr): void $closure
     */
    public function that(Closure $closure): ShouldInterface;
}
