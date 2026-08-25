<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Contracts;

use StructuraPhp\Structura\Benchmarks\Fixture\Exceptions\OrderNotFoundException;

/**
 * @template TModel of object
 */
interface RepositoryInterface
{
    /**
     * @return TModel
     *
     * @throws OrderNotFoundException
     */
    public function find(int $id): object;

    /**
     * @return array<int, TModel>
     */
    public function all(): array;
}
