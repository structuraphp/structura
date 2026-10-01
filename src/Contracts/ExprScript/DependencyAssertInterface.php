<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\ExprScript;

interface DependencyAssertInterface
{
    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function toOnlyDependOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,string>|string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function toOnlyDependOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,string>|string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function toNotDependOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toNotDependOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns to match phpDoc class names against
     */
    public function toOnlyDependOnPhpDoc(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match phpDoc class names against
     */
    public function toNotDependOnPhpDoc(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;
}
