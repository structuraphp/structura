<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Concerns;

trait Cacheable
{
    /** @var array<string, string> */
    private array $cache = [];

    public function remember(string $key, string $value): string
    {
        return $this->cache[$key] ??= $value;
    }

    public function forget(string $key): void
    {
        unset($this->cache[$key]);
    }
}
