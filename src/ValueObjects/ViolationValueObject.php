<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use Stringable;

readonly class ViolationValueObject implements Stringable
{
    public function __construct(
        public string $messageViolation,
        public string $assertClassname,
        public int $line,
        public ?string $pathname,
        public string $messageCustom,
    ) {}

    public function __toString(): string
    {
        return $this->messageViolation;
    }
}
