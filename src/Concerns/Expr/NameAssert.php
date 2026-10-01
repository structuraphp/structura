<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToHaveSuffix;

/**
 * @mixin AbstractExpr
 */
trait NameAssert
{
    public function toHavePrefix(string $prefix, string $message = ''): self
    {
        return $this->addExpr(new ToHavePrefix($prefix, $message));
    }

    public function toHaveSuffix(string $suffix, string $message = ''): self
    {
        return $this->addExpr(new ToHaveSuffix($suffix, $message));
    }
}
