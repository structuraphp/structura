<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Models;

use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;

final class Product extends Model
{
    protected const TABLE = 'products';

    private string $name = '';

    private int $price = 0;

    public function rename(string $name): void
    {
        $this->name = ucfirst(strtolower($name));
    }

    public function priceIn(Currency $currency): string
    {
        return sprintf('%s%0.2f', $currency->symbol(), $this->price / 100);
    }
}
