<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET',
    ) {}
}
