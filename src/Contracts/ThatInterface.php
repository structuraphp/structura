<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use StructuraPhp\Structura\AbstractExpr;

/**
 * @template T of AbstractExpr
 *
 * @extends ShouldInterface<T>
 */
interface ThatInterface extends ShouldInterface
{
    /**
     * @param Closure(T $expr): (T|void) $closure
     *
     * @return ShouldInterface<T>
     */
    public function that(Closure $closure): ShouldInterface;
}
