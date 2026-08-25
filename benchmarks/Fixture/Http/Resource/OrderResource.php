<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http\Resource;

use StructuraPhp\Structura\Benchmarks\Fixture\Dto\OrderDto;
use StructuraPhp\Structura\Benchmarks\Fixture\Http\ResourceBase;

final class OrderResource extends ResourceBase
{
    public function __construct(
        private readonly OrderDto $orderDto,
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->orderDto->id,
            'status' => $this->orderDto->status->label(),
            'total' => $this->orderDto->total(),
        ];
    }
}
