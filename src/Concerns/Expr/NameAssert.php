<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToHaveSuffix;
use StructuraPhp\Structura\Asserts\ToNotHavePrefix;
use StructuraPhp\Structura\Asserts\ToNotHaveSuffix;

/**
 * @mixin AbstractExpr
 */
trait NameAssert
{
    public function toHavePrefix(string $prefix, string $message = ''): self
    {
        return $this->addExpr(new ToHavePrefix($prefix, $message));
    }

    public function toNotHavePrefix(string $prefix, string $message = ''): self
    {
        return $this->addExpr(new ToNotHavePrefix($prefix, $message));
    }

    public function toHaveSuffix(string $suffix, string $message = ''): self
    {
        return $this->addExpr(new ToHaveSuffix($suffix, $message));
    }

    public function toNotHaveSuffix(string $suffix, string $message = ''): self
    {
        return $this->addExpr(new ToNotHaveSuffix($suffix, $message));
    }
}
