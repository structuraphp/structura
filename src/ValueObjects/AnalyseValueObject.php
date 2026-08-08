<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

/**
 * @phpstan-type ViolationsByTest array<string, array<int, ViolationValueObject>>
 * @phpstan-type WarningByTest array<string, array<int, string>>
 * @phpstan-type NoticesByTest array<string, string>
 */
final readonly class AnalyseValueObject
{
    /**
     * @param array<int,AnalyseTestValueObject> $analyseTestValueObjects
     */
    public function __construct(
        public float $timeStart,
        public int $countPass,
        public int $countViolation,
        public int $countWarning,
        public int $countNotice,
        public array $analyseTestValueObjects,
    ) {}

    public static function merge(float $timeStart, self ...$results): self
    {
        return new self(
            timeStart: $timeStart,
            countPass: array_sum(array_column($results, 'countPass')),
            countViolation: array_sum(array_column($results, 'countViolation')),
            countWarning: array_sum(array_column($results, 'countWarning')),
            countNotice: array_sum(array_column($results, 'countNotice')),
            analyseTestValueObjects: array_merge(...array_column($results, 'analyseTestValueObjects')),
        );
    }

    /**
     * @return ViolationsByTest
     */
    public function getViolations(): array
    {
        $result = [];
        foreach ($this->analyseTestValueObjects as $testResult) {
            foreach ($testResult->assertValueObject->violations as $key => $violations) {
                $result[$key] = array_merge($result[$key] ?? [], $violations);
            }
        }

        return $result;
    }

    /**
     * @return WarningByTest
     */
    public function getWarnings(): array
    {
        $result = [];
        foreach ($this->analyseTestValueObjects as $testResult) {
            foreach ($testResult->assertValueObject->warnings as $key => $warnings) {
                $result[$key] = array_merge($result[$key] ?? [], $warnings);
            }
        }

        return $result;
    }

    /**
     * @return NoticesByTest
     */
    public function getNotices(): array
    {
        $result = [];
        foreach ($this->analyseTestValueObjects as $testResult) {
            foreach ($testResult->assertValueObject->notices as $key => $notice) {
                $result[$key] = $notice;
            }
        }

        return $result;
    }
}
