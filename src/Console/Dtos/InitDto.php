<?php

declare(strict_types=1);

namespace Structura\Console\Dtos;

use ArrayAccess;
use InvalidArgumentException;

class InitDto
{
    public function __construct(
        public readonly string $configPath,
    ) {}

    /**
     * @param ArrayAccess<string,scalar>|array<string,scalar> $data
     */
    public static function fromArray(ArrayAccess|array $data): self
    {
        return new self(
            configPath: \is_string($data['config'])
                ? $data['config']
                : throw new InvalidArgumentException(),
        );
    }
}
