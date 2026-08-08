<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class SourceTestValueObject
{
    public function __construct(
        public string $testClassname,
        public string $textDox,
        public string $methodName,
        public int $line,
        public string $pathname,
    ) {}
}
