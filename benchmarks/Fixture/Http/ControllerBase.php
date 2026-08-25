<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http;

use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\ControllerInterface;

abstract class ControllerBase implements ControllerInterface
{
    /**
     * @param array<string, string> $payload
     *
     * @return array<string, string>
     */
    protected function json(array $payload): array
    {
        return array_map(static fn (string $value): string => trim($value), $payload);
    }
}
