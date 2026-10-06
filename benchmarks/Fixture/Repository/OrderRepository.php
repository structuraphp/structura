<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Repository;

use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\RepositoryInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Exceptions\OrderNotFoundException;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Order;

/**
 * @implements RepositoryInterface<Order>
 */
final class OrderRepository implements RepositoryInterface
{
    /** @var array<int, Order> */
    private array $orders = [];

    public function find(int $id): Order
    {
        return $this->orders[$id] ?? throw OrderNotFoundException::withId($id);
    }

    /**
     * @return array<int, Order>
     */
    public function all(): array
    {
        return array_values($this->orders);
    }

    public function save(Order $order): void
    {
        $this->orders[$order->id()] = $order;
    }
}
