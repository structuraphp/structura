<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut;
use StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation;
use StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance;
use StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait;
use StructuraPhp\Structura\Expr;

/**
 * @mixin AbstractExpr&Expr
 */
trait DependencyAssert
{
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
}
