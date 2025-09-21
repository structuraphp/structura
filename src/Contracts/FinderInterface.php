<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use Symfony\Component\Finder\Finder;

/**
 * @template T of AbstractExpr
 */
interface FinderInterface
{
    /**
     * @param array<int,string>|string $dirs
     * @param null|Closure(Finder): ?Finder $closure
     *
     * @return ThatInterface<T>
     */
    public function fromDir(array|string $dirs, ?Closure $closure): ThatInterface;

    /**
     * @return ThatInterface<T>
     */
    public function fromRaw(string $raw, string $pathname = ''): ThatInterface;
}
