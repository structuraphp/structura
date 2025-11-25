<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class MakeTestValueObject
{
    public function __construct(
        public string $testClassName,
        public string $path = 'src',
    ) {}
}
