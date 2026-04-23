<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\NotToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToBeInOneOfTheNamespaces;

/**
 * @mixin AbstractExpr
 */
trait ThridPartyAssert
{
    public function toBeInOneOfTheNamespaces(
        array|string $patterns,
        string $message = '',
    ): self {
        return $this->addExpr(new ToBeInOneOfTheNamespaces((array) $patterns, $message));
    }

    public function notToBeInOneOfTheNamespaces(
        array|string $patterns,
        string $message = '',
    ): self {
        return $this->addExpr(new NotToBeInOneOfTheNamespaces((array) $patterns, $message));
    }
}
