<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns;

trait Pipe
{
    /**
     * Pipe operator polyfill.
     *
     * @template T
     *
     * @param callable(T): T ...$stages
     *
     * @return callable(T): T
     */
    private function pipe(callable ...$stages): callable
    {
        return fn ($input) => array_reduce(
            $stages,
            static fn ($input, callable $next): mixed => $next($input),
            $input,
        );
    }
}
