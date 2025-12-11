<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToHaveSuffix;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprIteratorAggregate;

/**
 * @mixin ExprIteratorAggregate<ExprInterface>
 */
trait NameAssert
{
    public function toHavePrefix(string $prefix): self
    {
        return $this->addExpr(new ToHavePrefix($prefix));
    }

    public function toHaveSuffix(string $suffix, string $message = ''): self
    {
        return $this->addExpr(new ToHaveSuffix($suffix, $message));
    }
}
