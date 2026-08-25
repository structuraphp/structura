<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Models;

use StructuraPhp\Structura\Benchmarks\Fixture\Concerns\Cacheable;
use StructuraPhp\Structura\Benchmarks\Fixture\Concerns\HasUuid;
use StructuraPhp\Structura\Benchmarks\Fixture\Dto\AddressDto;

final class Customer extends Model
{
    use Cacheable;
    use HasUuid;

    protected const TABLE = 'customers';

    private ?AddressDto $address = null;

    public function withAddress(AddressDto $addressDto): self
    {
        $this->address = $addressDto;

        return $this;
    }

    public function address(): ?AddressDto
    {
        return $this->address;
    }

    public function label(): string
    {
        return $this->remember('label', sprintf('customer-%d', $this->id));
    }
}
