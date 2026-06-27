<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class SourceTestValueObject
{
    public function __construct(
        public string $classname,
        public string $textDox,
        public string $methodName,
        public string $filePath,
    ) {}
}
