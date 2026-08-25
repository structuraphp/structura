<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Jobs;

use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\JobInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\ShouldQueueInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Invoice;

final class SendInvoiceJob implements JobInterface, ShouldQueueInterface
{
    public function __construct(
        private readonly Invoice $invoice,
    ) {}

    public function handle(): void
    {
        $this->invoice->assertPaid();
    }

    public function queue(): string
    {
        return 'invoices';
    }
}
