<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Dto;

use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;
use StructuraPhp\Structura\Benchmarks\Fixture\Enums\OrderStatus;

final readonly class OrderDto
{
    /**
     * @param array<int, OrderLineDto> $lines
     */
    public function __construct(
        public int $id,
        public CustomerDto $customer,
        public OrderStatus $status,
        public Currency $currency,
        public array $lines = [],
    ) {}

    public function total(): int
    {
        return array_sum(array_map(static fn (OrderLineDto $line): int => $line->subtotal(), $this->lines));
    }
}
