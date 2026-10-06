<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\ExprScript;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToNotDependOn;
use StructuraPhp\Structura\Asserts\ToNotDependOnFunction;
use StructuraPhp\Structura\Asserts\ToNotDependOnPhpDoc;
use StructuraPhp\Structura\Asserts\ToOnlyDependOn;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnFunction;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnPhpDoc;

/**
 * @mixin AbstractExpr
 */
trait DependencyAssert
{
    public function toOnlyDependOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOn((array) $names, (array) $patterns, $message),
        );
    }

    public function toOnlyDependOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOnFunction((array) $names, (array) $patterns, $message),
        );
    }

    public function toNotDependOnFunction(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToNotDependOnFunction((array) $names, (array) $patterns, $message),
        );
    }

    public function toNotDependOn(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToNotDependOn((array) $names, (array) $patterns, $message),
        );
    }

    public function toOnlyDependOnPhpDoc(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToOnlyDependOnPhpDoc((array) $names, (array) $patterns, $message),
        );
    }

    public function toNotDependOnPhpDoc(
        array|string $names = [],
        array|string $patterns = [],
        string $message = '',
    ): self {
        return $this->addExpr(
            new ToNotDependOnPhpDoc((array) $names, (array) $patterns, $message),
        );
    }
}
