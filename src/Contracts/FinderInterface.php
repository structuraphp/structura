<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use Symfony\Component\Finder\Finder;

interface FinderInterface
{
    /**
     * @param array<int,string>|string $dirs
     * @param null|Closure(Finder): ?Finder $closure
     */
    public function fromDir(array|string $dirs, ?Closure $closure): ThatInterface;

    public function fromRaw(string $raw): ThatInterface;
}
