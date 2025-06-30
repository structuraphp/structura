<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface DependencyAssertInterface
{
    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function dependsOnlyOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function dependsOnlyOnAttribut(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function dependsOnlyOnImplementation(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function dependsOnlyOnInheritance(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function dependsOnlyOnUseTrait(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,string>|string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function dependsOnlyOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,string>|string $names
     * @param array<int,string>|string $patterns regex patterns to match class names against
     */
    public function toNotDependsOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;

    /**
     * @param array<int,class-string>|class-string $names
     * @param array<int,string>|string $patterns regex patterns not to match class names against
     */
    public function toNotDependsOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self;
}
