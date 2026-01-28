<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Dtos;

use ArrayAccess;
use InvalidArgumentException;
use StructuraPhp\Structura\Console\Enums\AnalyseOption;
use StructuraPhp\Structura\Console\Enums\CommonOption;

final readonly class AnalyzeDto
{
    public function __construct(
        public string $configPath,
        public string $errorFormat,
        public string $progressFormat,
        public bool $stopOnError,
        public bool $stopOnWarning,
        public bool $stopOnNotice,
        public ?string $filter,
        public ?string $testSuite,
    ) {}

    /**
     * @param array<string,null|scalar>|ArrayAccess<string,null|scalar> $data
     */
    public static function fromArray(array|ArrayAccess $data): self
    {
        return new self(
            configPath: \is_string($data[CommonOption::Config->value])
                ? $data[CommonOption::Config->value]
                : throw new InvalidArgumentException('config must be a string'),
            errorFormat: \is_string($data[AnalyseOption::ErrorFormat->value])
                ? $data[AnalyseOption::ErrorFormat->value]
                : throw new InvalidArgumentException('error format must be a string'),
            progressFormat: \is_string($data[AnalyseOption::ProgressFormat->value])
                ? $data[AnalyseOption::ProgressFormat->value]
                : throw new InvalidArgumentException('progress must be a string'),
            stopOnError: \is_bool($data[AnalyseOption::StopOnError->value])
                ? $data[AnalyseOption::StopOnError->value]
                : throw new InvalidArgumentException('stop on error must be a bool'),
            stopOnWarning: \is_bool($data[AnalyseOption::StopOnWarning->value])
                ? $data[AnalyseOption::StopOnWarning->value]
                : throw new InvalidArgumentException('stop on warning must be a bool'),
            stopOnNotice: \is_bool($data[AnalyseOption::StopOnNotice->value])
                ? $data[AnalyseOption::StopOnNotice->value]
                : throw new InvalidArgumentException('stop on notice must be a bool'),
            filter: \is_string($data[AnalyseOption::Filter->value]) || is_null($data[AnalyseOption::Filter->value])
                ? $data[AnalyseOption::Filter->value]
                : throw new InvalidArgumentException('filter must be a string or null'),
            testSuite: \is_string($data[AnalyseOption::Testsuite->value]) || is_null($data[AnalyseOption::Testsuite->value])
                ? $data[AnalyseOption::Testsuite->value]
                : throw new InvalidArgumentException('testsuite must be a string or null'),
        );
    }
}
