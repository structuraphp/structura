<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use ArrayAccess;
use InvalidArgumentException;

final readonly class MakeTestValueObject
{
    public function __construct(
        public string $testClassName,
        public string $path = 'src',
    ) {}

    /**
     * @param array<string,scalar>|ArrayAccess<string,scalar> $data
     */
    public static function fromArray(array|ArrayAccess $data): self
    {
        return new self(
            testClassName: \is_string($data['name'])
                ? $data['name']
                : throw new InvalidArgumentException(),
            path: isset($data['path']) && \is_string($data['path'])
                ? $data['path']
                : 'src',
        );
    }
}
