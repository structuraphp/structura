<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface DependencyAssertInterface
{
    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toOnlyDependOnAttribute(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toOnlyDependOnImplementation(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toOnlyDependOnInheritance(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toOnlyDependOnUseTrait(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;
}
