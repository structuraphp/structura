<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Builder\RuleBuilder;

/**
 * @template T of AbstractExpr
 */
interface ShouldInterface
{
    /**
     * @return ShouldInterface<T>
     */
    public function except(Closure $closure): ShouldInterface;

    /**
     * @param Closure(T): (T|void) $closure
     */
    public function should(Closure $closure): RuleBuilder;
}
