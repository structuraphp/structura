<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class RootNamespaceValueObject
{
    public function __construct(
        public string $namespace,
        public string $directory,
    ) {}
}
