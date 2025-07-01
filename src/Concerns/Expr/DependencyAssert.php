<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut;
use StructuraPhp\Structura\Asserts\DependsOnlyOnFunction;
use StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation;
use StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance;
use StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait;
use StructuraPhp\Structura\Asserts\ToNotDependsOn;
use StructuraPhp\Structura\Asserts\ToNotDependsOnFunction;
use StructuraPhp\Structura\Expr;

/**
 * @mixin Expr
 */
trait DependencyAssert
{
    public function dependsOnlyOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOn((array) $names, (array) $patterns, $message),
        );
    }

    public function dependsOnlyOnAttribut(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOnAttribut(
                array_unique(array_merge((array) $names, ...$this->attributDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function dependsOnlyOnImplementation(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOnImplementation(
                array_unique(array_merge((array) $names, ...$this->implementDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function dependsOnlyOnInheritance(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOnInheritance(
                array_unique(array_merge((array) $names, ...$this->extendDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function dependsOnlyOnUseTrait(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOnUseTrait(
                array_unique(array_merge((array) $names, ...$this->traitDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function dependsOnlyOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new DependsOnlyOnFunction((array) $names, (array) $patterns, $message),
        );
    }

    public function toNotDependsOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToNotDependsOnFunction((array) $names, (array) $patterns, $message),
        );
    }

    public function toNotDependsOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToNotDependsOn((array) $names, (array) $patterns, $message),
        );
    }
}
