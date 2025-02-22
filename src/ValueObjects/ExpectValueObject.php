<?php

declare(strict_types=1);

namespace Structura\ValueObjects;

class ExpectValueObject
{
    /**
     * @param array<int,string> $classes
     */
    public function __construct(
        public readonly array $classes,
    ) {}
}
