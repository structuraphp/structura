<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToBeInNamespaces;
use StructuraPhp\Structura\Asserts\ToNotBeInNamespaces;

/**
 * @mixin AbstractExpr
 */
trait ThridPartyAssert
{
    public function toBeInNamespaces(
        array|string $patterns,
        string $message = '',
    ): self {
        return $this->addExpr(new ToBeInNamespaces((array) $patterns, $message));
    }

    public function toNotBeInNamespaces(
        array|string $patterns,
        string $message = '',
    ): self {
        return $this->addExpr(new ToNotBeInNamespaces((array) $patterns, $message));
    }
}
