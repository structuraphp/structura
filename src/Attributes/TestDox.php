<?php

declare(strict_types=1);

namespace Structura\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class TestDox
{
    public function __construct(public string $value) {}
}
