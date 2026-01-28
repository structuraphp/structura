<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

/**
 * @phpstan-import-type ViolationsByTest from \StructuraPhp\Structura\ValueObjects\AnalyseValueObject
 * @phpstan-import-type WarningByTest from \StructuraPhp\Structura\ValueObjects\AnalyseValueObject
 */
final readonly class AssertValueObject
{
    /**
     * @param array<string,int> $pass
     * @param ViolationsByTest $violations
     * @param array<string, array<int, string>> $exceptions
     * @param WarningByTest $warnings
     * @param array<string, string> $notices
     */
    public function __construct(
        public array $pass = [],
        public array $violations = [],
        public array $exceptions = [],
        public array $warnings = [],
        public array $notices = [],
    ) {}

    public function countViolation(string $key): int
    {
        return \count($this->violations[$key] ?? []);
    }

    public function countWarning(string $key): int
    {
        return \count($this->warnings[$key] ?? []);
    }

    public function countAssertsFailure(): int
    {
        return array_count_values($this->pass)[0] ?? 0;
    }

    public function countAssertsSuccess(): int
    {
        return array_count_values($this->pass)[1] ?? 0;
    }

    public function countAssertsWarning(): int
    {
        return array_count_values($this->pass)[2] ?? 0;
    }

    public function countAssertsNotices(): int
    {
        return array_count_values($this->pass)[3] ?? 0;
    }
}
