<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use Stringable;

class ViolationValueObject implements Stringable
{
    public function __construct(
        public readonly string $messageViolation,
        public readonly string $assertClassname,
        public readonly int $line,
        public readonly ?string $pathname,
        public readonly string $messageCustom,
    ) {}

    public function __toString(): string
    {
        return $this->messageViolation;
    }
}
