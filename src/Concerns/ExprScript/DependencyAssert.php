<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\ExprScript;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Asserts\DependsOnlyOnFunction;
use StructuraPhp\Structura\Asserts\ToNotDependsOn;
use StructuraPhp\Structura\Asserts\ToNotDependsOnFunction;

/**
 * @mixin AbstractExpr
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
