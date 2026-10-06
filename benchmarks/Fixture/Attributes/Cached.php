<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Cached
{
    public function __construct(
        public int $ttl = 60,
        public string $store = 'default',
    ) {}
}
