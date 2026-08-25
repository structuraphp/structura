<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Enums;

enum Weekday
{
    case Monday;
    case Tuesday;
    case Wednesday;
    case Thursday;
    case Friday;

    public function isStartOfWeek(): bool
    {
        return $this === self::Monday;
    }
}
