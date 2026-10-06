<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Dto;

final readonly class OrderLineDto
{
    public function __construct(
        public string $label,
        public int $unitPrice,
        public int $quantity = 1,
    ) {}

    public function subtotal(): int
    {
        return $this->unitPrice * $this->quantity;
    }
}
