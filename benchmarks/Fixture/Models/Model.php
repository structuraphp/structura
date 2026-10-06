<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Models;

use StructuraPhp\Structura\Benchmarks\Fixture\Concerns\HasTimestamps;

abstract class Model
{
    use HasTimestamps;

    protected const TABLE = '';

    public function __construct(
        protected int $id = 0,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function table(): string
    {
        return static::TABLE;
    }
}
