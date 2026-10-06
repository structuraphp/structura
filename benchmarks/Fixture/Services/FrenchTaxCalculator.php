<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Services;

final class FrenchTaxCalculator extends TaxCalculator
{
    protected function rate(): float
    {
        return 0.2;
    }
}
