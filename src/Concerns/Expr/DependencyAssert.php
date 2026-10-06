<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnAttribute;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnImplementation;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnInheritance;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnUseTrait;
use StructuraPhp\Structura\Expr;

/**
 * @mixin AbstractExpr&Expr
 */
trait DependencyAssert
{
    public function toOnlyDependOnAttribute(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOnAttribute(
                array_unique(array_merge((array) $names, ...$this->attributDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function toOnlyDependOnImplementation(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOnImplementation(
                array_unique(array_merge((array) $names, ...$this->implementDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function toOnlyDependOnInheritance(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOnInheritance(
                array_unique(array_merge((array) $names, ...$this->extendDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }

    public function toOnlyDependOnUseTrait(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOnUseTrait(
                array_unique(array_merge((array) $names, ...$this->traitDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }
}
