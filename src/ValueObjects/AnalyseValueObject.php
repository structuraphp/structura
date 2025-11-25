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
     * @param array<int,AnalyseTestValueObject> $analyseTestValueObjects
     */
    public function __construct(
        public float $timeStart,
        public int $countPass,
        public int $countViolation,
        public int $countWarning,
        public array $violationsByTests,
        public array $analyseTestValueObjects,
    ) {}
}
