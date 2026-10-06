<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Enums;

enum Currency: string
{
    case Eur = 'EUR';
    case Usd = 'USD';
    case Gbp = 'GBP';

    public function symbol(): string
    {
        return match ($this) {
            self::Eur => '€',
            self::Usd => '$',
            self::Gbp => '£',
        };
    }
}
