<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Dtos;

use ArrayAccess;
use InvalidArgumentException;

final readonly class InitDto
{
    public function __construct(
        public string $configPath,
    ) {}

    /**
     * @param array<string,scalar>|ArrayAccess<string,scalar> $data
     */
    public static function fromArray(array|ArrayAccess $data): self
    {
        return new self(
            configPath: \is_string($data['config'])
                ? $data['config']
                : throw new InvalidArgumentException(),
        );
    }
}
