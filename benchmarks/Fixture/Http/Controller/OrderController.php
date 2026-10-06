<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http\Controller;

use StructuraPhp\Structura\Benchmarks\Fixture\Attributes\Route;
use StructuraPhp\Structura\Benchmarks\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Benchmarks\Fixture\Repository\OrderRepository;

final class OrderController extends ControllerBase
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {}

    #[Route('/orders')]
    public function __invoke(): array
    {
        return $this->json([
            'count' => (string) count($this->orderRepository->all()),
        ]);
    }
}
