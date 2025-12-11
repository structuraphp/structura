<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut;
use StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation;
use StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance;
use StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprIteratorAggregate;
use StructuraPhp\Structura\Contracts\ShadowDependenciesInterface;

/**
 * @mixin ExprIteratorAggregate<ExprInterface>
 */
trait DependencyAssert
{
    public function dependsOnlyOnAttribut(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        $attributDependencies = $this instanceof ShadowDependenciesInterface
            ? $this->getAttributs()
            : [];

        return $this->addExpr(
            new DependsOnlyOnAttribut(
                array_unique(array_merge((array) $names, ...$attributDependencies)),
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
        $implementDependencies = $this instanceof ShadowDependenciesInterface
            ? $this->getImplements()
            : [];

        return $this->addExpr(
            new DependsOnlyOnImplementation(
                array_unique(array_merge((array) $names, ...$implementDependencies)),
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
        $extendDependencies = $this instanceof ShadowDependenciesInterface
            ? $this->getExtends()
            : [];

        return $this->addExpr(
            new DependsOnlyOnInheritance(
                array_unique(array_merge((array) $names, ...$extendDependencies)),
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
        $traitDependencies = $this instanceof ShadowDependenciesInterface
            ? $this->getTraits()
            : [];

        return $this->addExpr(
            new DependsOnlyOnUseTrait(
                array_unique(array_merge((array) $names, ...$traitDependencies)),
                (array) $patterns,
                $message,
            ),
        );
    }
}
