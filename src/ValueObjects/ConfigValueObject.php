<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;

final readonly class ConfigValueObject
{
    /**
     * @param array<string,string> $testSuites
     * @param array<string,ErrorFormatterInterface> $errorFormatter
     * @param array<string,ProgressFormatterInterface> $progressFormatter
     * @param array<int,string> $extensions
     */
    public function __construct(
        public array $testSuites,
        public ?RootNamespaceValueObject $rootNamespace,
        public array $errorFormatter,
        public array $progressFormatter,
        public array $extensions,
        public ?string $autoload,
    ) {}
}
