<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Fixture\Dto;

final readonly class UserDto
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
