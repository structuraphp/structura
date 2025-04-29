<?php

declare(strict_types=1);

namespace Structura\ValueObjects;

final readonly class GenerateTestValueObject
{
    public function __construct(
        public string $content,
        public string $filename,
        public string $className,
    ) {}
}
