<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final readonly class RuleSutValuesObject
{
    /**
     * @param array<int, class-string> $expects
     */
    public function __construct(
        public RootNamespaceValueObject $appRootNamespace,
        public RootNamespaceValueObject $testRootNamespace,
        public array $expects,
    ) {}
}
