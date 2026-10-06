<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Enums;

enum OrderStatus: int
{
    case Draft = 0;
    case Pending = 1;
    case Paid = 2;
    case Shipped = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'draft',
            self::Pending => 'pending',
            self::Paid => 'paid',
            self::Shipped => 'shipped',
            self::Cancelled => 'cancelled',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Shipped, self::Cancelled], true);
    }
}
