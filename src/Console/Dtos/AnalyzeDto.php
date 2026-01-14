<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Dtos;

use ArrayAccess;
use InvalidArgumentException;

final readonly class AnalyzeDto
{
    public const ERROR_FORMAT_OPTION = 'error-format';

    public const PROGRESS_FORMAT_OPTION = 'progress-format';

    public const STOP_ON_ERROR = 'stop-on-error';

    public const STOP_ON_WARNING = 'stop-on-warning';

    public const FILTER = 'filter';

    public const TESTSUITE = 'testsuite';

    public function __construct(
        public string $configPath,
        public string $errorFormat,
        public string $progressFormat,
        public bool $stopOnError,
        public bool $stopOnWarning,
        public ?string $filter,
        public ?string $testSuite,
    ) {}

    /**
     * @param array<string,null|scalar>|ArrayAccess<string,null|scalar> $data
     */
    public static function fromArray(array|ArrayAccess $data): self
    {
        return new self(
            configPath: \is_string($data['config'])
                ? $data['config']
                : throw new InvalidArgumentException('config must be a string'),
            errorFormat: \is_string($data[self::ERROR_FORMAT_OPTION])
                ? $data[self::ERROR_FORMAT_OPTION]
                : throw new InvalidArgumentException('error format must be a string'),
            progressFormat: \is_string($data[self::PROGRESS_FORMAT_OPTION])
                ? $data[self::PROGRESS_FORMAT_OPTION]
                : throw new InvalidArgumentException('progress must be a string'),
            stopOnError: \is_bool($data[self::STOP_ON_ERROR])
                ? $data[self::STOP_ON_ERROR]
                : throw new InvalidArgumentException('stop on error must be a bool'),
            stopOnWarning: \is_bool($data[self::STOP_ON_WARNING])
                ? $data[self::STOP_ON_WARNING]
                : throw new InvalidArgumentException('stop on warning must be a bool'),
            filter: \is_string($data[self::FILTER]) || is_null($data[self::FILTER])
                ? $data[self::FILTER]
                : throw new InvalidArgumentException('filter must be a string or null'),
            testSuite: \is_string($data[self::TESTSUITE]) || is_null($data[self::TESTSUITE])
                ? $data[self::TESTSUITE]
                : throw new InvalidArgumentException('testsuite must be a string or null'),
        );
    }
}
