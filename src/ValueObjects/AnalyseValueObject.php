<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

/**
 * @phpstan-type ViolationsByTest array<string, array<int, ViolationValueObject>>
 */
final readonly class AnalyseValueObject
{
    /**
     * @param array<int,ViolationsByTest> $violationsByTests
     * @param array<int,string> $prints
     */
    public function __construct(
        public int $countPass,
        public int $countViolation,
        public array $violationsByTests,
        public array $prints = [],
    ) {}
}
