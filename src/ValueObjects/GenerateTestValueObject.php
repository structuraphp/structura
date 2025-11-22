<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class GenerateTestValueObject
{
    public function __construct(
        public string $content,
        public string $filename,
    ) {}
}
