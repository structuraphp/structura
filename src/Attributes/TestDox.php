<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class TestDox
{
    public function __construct(public string $value) {}
}
