<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

/**
 * @phpstan-type ViolationsByTest array<string, array<int, ViolationValueObject>>
 * @phpstan-type WarningByTest array<string, array<int, string>>
 */
final readonly class AnalyseValueObject
{
    /**
     * @param array<int,ViolationsByTest> $violationsByTests
     * @param array<int,WarningByTest> $warningsByTests
     * @param array<int, array<string, string>> $noticeByTests
     * @param array<int,AnalyseTestValueObject> $analyseTestValueObjects
     */
    public function __construct(
        public float $timeStart,
        public int $countPass,
        public int $countViolation,
        public int $countWarning,
        public int $countNotice,
        public array $violationsByTests,
        public array $warningsByTests,
        public array $noticeByTests,
        public array $analyseTestValueObjects,
    ) {}
}
