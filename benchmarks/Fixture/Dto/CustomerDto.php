<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Dto;

final readonly class CustomerDto
{
    public const DEFAULT_LOCALE = 'fr_FR';

    public function __construct(
        public int $id,
        public string $email,
        public AddressDto $address,
        public string $locale = self::DEFAULT_LOCALE,
    ) {}

    public function domain(): string
    {
        return substr(strrchr($this->email, '@') ?: '@', 1);
    }
}
