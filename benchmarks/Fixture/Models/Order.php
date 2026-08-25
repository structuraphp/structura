<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Models;

use StructuraPhp\Structura\Benchmarks\Fixture\Attributes\Cached;
use StructuraPhp\Structura\Benchmarks\Fixture\Concerns\HasUuid;
use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;
use StructuraPhp\Structura\Benchmarks\Fixture\Enums\OrderStatus;

#[Cached(ttl: 120)]
final class Order extends Model
{
    use HasUuid;

    public const MAX_LINES = 50;

    protected const TABLE = 'orders';

    /** @var array<int, string> */
    private array $lines = [];

    public function __construct(
        int $id = 0,
        private OrderStatus $status = OrderStatus::Draft,
        private readonly Currency $currency = Currency::Eur,
    ) {
        parent::__construct($id);
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function pay(): void
    {
        $this->status = OrderStatus::Paid;
        $this->touch();
    }

    public function addLine(string $label): void
    {
        if (count($this->lines) >= self::MAX_LINES) {
            return;
        }

        $this->lines[] = strtolower(trim($label));
    }
}
