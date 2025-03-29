<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class AnalyseSutValueObject
{
    /**
     * @param array<string, string> $violations
     */
    public function __construct(
        public int $countPass,
        public int $countViolation,
        public array $violations,
    ) {}
}
