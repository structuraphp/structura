<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http;

abstract class ResourceBase
{
    /**
     * @return array<string, int|string>
     */
    abstract public function toArray(): array;
}
