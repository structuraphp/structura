<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Models;

use DateTimeImmutable;
use StructuraPhp\Structura\Benchmarks\Fixture\Exceptions\PaymentFailedException;

final class Invoice extends Model
{
    protected const TABLE = 'invoices';

    private ?DateTimeImmutable $paidAt = null;

    public function markAsPaid(DateTimeImmutable $dateTimeImmutable): void
    {
        $this->paidAt = $dateTimeImmutable;
    }

    /**
     * @throws PaymentFailedException
     */
    public function assertPaid(): void
    {
        if (!$this->paidAt instanceof DateTimeImmutable) {
            throw new PaymentFailedException('Invoice is not paid');
        }
    }
}
