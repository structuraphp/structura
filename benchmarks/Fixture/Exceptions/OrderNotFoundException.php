<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Exceptions;

use RuntimeException;

final class OrderNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Order "%d" not found', $id));
    }
}
