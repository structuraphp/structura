<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Dto;

final readonly class AddressDto
{
    public function __construct(
        public string $street,
        public string $city,
        public string $zipCode,
        public string $country = 'FR',
    ) {}

    public function __toString(): string
    {
        return sprintf('%s, %s %s (%s)', $this->street, $this->zipCode, $this->city, $this->country);
    }
}
