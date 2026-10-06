<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Services;

use StructuraPhp\Structura\Benchmarks\Fixture\Dto\OrderDto;
use StructuraPhp\Structura\Benchmarks\Fixture\Dto\OrderLineDto;
use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;

final readonly class PricingService
{
    public function __construct(
        private TaxCalculator $taxCalculator,
    ) {}

    public function total(OrderDto $orderDto): float
    {
        return $this->taxCalculator->apply($orderDto->total(), $orderDto->currency);
    }

    /**
     * @param array<int, OrderLineDto> $lines
     *
     * @return array<int, string>
     */
    public function breakdown(array $lines, Currency $currency): array
    {
        return array_map(
            static fn (OrderLineDto $orderLineDto): string => sprintf(
                '%s: %s%d',
                $orderLineDto->label,
                $currency->symbol(),
                $orderLineDto->subtotal(),
            ),
            $lines,
        );
    }
}
