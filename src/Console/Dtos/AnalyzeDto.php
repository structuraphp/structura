<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Dtos;

use ArrayAccess;
use InvalidArgumentException;

final readonly class AnalyzeDto
{
    public const ERROR_FORMAT_OPTION = 'error-format';

    public const PROGRESS_FORMAT_OPTION = 'progress-format';

    public function __construct(
        public string $configPath,
        public string $errorFormat,
        public string $progressFormat,
    ) {}

    /**
     * @param array<string,scalar>|ArrayAccess<string,scalar> $data
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
        );
    }
}
