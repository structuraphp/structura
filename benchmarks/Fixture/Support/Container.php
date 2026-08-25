<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Support;

use StructuraPhp\Structura\Benchmarks\Fixture\Exceptions\InvalidAddressException;

final class Container
{
    /** @var array<class-string, object> */
    private array $bindings = [];

    /**
     * @template TService of object
     *
     * @param class-string<TService> $id
     * @param TService $service
     */
    public function bind(string $id, object $service): void
    {
        $this->bindings[$id] = $service;
    }

    /**
     * @param class-string $id
     *
     * @throws InvalidAddressException
     */
    public function get(string $id): object
    {
        return $this->bindings[$id] ?? throw new InvalidAddressException($id);
    }
}
