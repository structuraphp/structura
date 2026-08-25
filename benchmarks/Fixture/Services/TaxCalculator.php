<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Services;

use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;

abstract class TaxCalculator
{
    protected const PRECISION = 2;

    public function apply(int $amount, Currency $currency): float
    {
        return round($amount * (1 + $this->rate()), self::PRECISION) * ($currency === Currency::Eur ? 1 : 1);
    }

    abstract protected function rate(): float;
}
