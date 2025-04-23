<?php

declare(strict_types=1);

namespace Structura\Console\Dtos;

use ArrayAccess;
use InvalidArgumentException;

class MakeTestDto
{
    public function __construct(
        public readonly string $configPath,
        public readonly string $testClassName,
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
            testClassName: \is_string($data['name'])
                ? $data['name']
                : throw new InvalidArgumentException(),
        );
    }
}
