<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Repository;

use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\RepositoryInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Customer;

/**
 * @implements RepositoryInterface<Customer>
 */
final class CustomerRepository implements RepositoryInterface
{
    /** @var array<int, Customer> */
    private array $customers = [];

    public function find(int $id): Customer
    {
        return $this->customers[$id] ??= new Customer($id);
    }

    /**
     * @return array<int, Customer>
     */
    public function all(): array
    {
        return array_values($this->customers);
    }
}
