<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Support;

final class Str
{
    public static function slug(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($value)) ?? '', '-');
    }

    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}
