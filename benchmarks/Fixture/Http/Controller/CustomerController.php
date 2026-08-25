<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http\Controller;

use StructuraPhp\Structura\Benchmarks\Fixture\Attributes\Route;
use StructuraPhp\Structura\Benchmarks\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Benchmarks\Fixture\Repository\CustomerRepository;

final class CustomerController extends ControllerBase
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {}

    #[Route('/customers', method: 'POST')]
    public function __invoke(): array
    {
        return $this->json([
            'label' => $this->customerRepository->find(1)->label(),
        ]);
    }
}
