<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http\Controller;

use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;
use StructuraPhp\Structura\Benchmarks\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Product;

final class ProductController extends ControllerBase
{
    public function __invoke(): array
    {
        $product = new Product();
        $product->rename('keyboard');

        return $this->json(['price' => $product->priceIn(Currency::Eur)]);
    }
}
