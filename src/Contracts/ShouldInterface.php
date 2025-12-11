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
     * @param array<int, string>|string $className file path or class name (with ::class) or array of both
     *
     * @return ShouldInterface<T>
     */
    public function except(array|string $className, Closure $closure): ShouldInterface;

    /**
     * @param Closure(T): (T|void) $closure
     */
    public function should(Closure $closure): RuleBuilder;
}
