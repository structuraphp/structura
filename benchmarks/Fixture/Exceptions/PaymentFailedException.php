<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Exceptions;

use DomainException;
use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Currency;

final class PaymentFailedException extends DomainException
{
    public static function forAmount(int $amount, Currency $currency): self
    {
        return new self(sprintf('Payment of %d %s failed', $amount, $currency->value));
    }
}
