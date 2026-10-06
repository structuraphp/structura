<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Http\Request;

use StructuraPhp\Structura\Benchmarks\Fixture\Enums\OrderStatus;

final class OrderRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'status' => implode('|', array_map(
                static fn (OrderStatus $orderStatus): string => $orderStatus->label(),
                OrderStatus::cases(),
            )),
            'currency' => 'required|string',
        ];
    }
}
