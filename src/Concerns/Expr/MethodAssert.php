<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Expr;

/**
 * @mixin Expr
 */
trait MethodAssert
{
    public function toHaveMethod(string $name, string $message = ''): self
    {
        return $this->addExpr(new ToHaveMethod($name, $message));
    }

    public function toHaveConstructor(string $message = ''): self
    {
        return $this->toHaveMethod('__construct', $message);
    }

    public function toHaveDestructor(string $message = ''): self
    {
        return $this->toHaveMethod('__destruct', $message);
    }
}
