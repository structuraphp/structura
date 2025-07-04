<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\Asserts\NotToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Expr;

/**
 * @mixin Expr
 */
trait OtherAssert
{
    public function toUseStrictTypes(string $message = ''): self
    {
        return $this->toUseDeclare('strict_types', '1', $message);
    }

    public function toUseDeclare(string $key, string $value, string $message = ''): self
    {
        return $this->addExpr(new ToUseDeclare($key, $value, $message));
    }

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
