<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Jobs;

use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\JobInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Repository\OrderRepository;

final class ExportOrdersJob implements JobInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {}

    public function handle(): void
    {
        foreach ($this->orderRepository->all() as $order) {
            file_put_contents('php://memory', serialize($order->uuid()));
        }
    }
}
