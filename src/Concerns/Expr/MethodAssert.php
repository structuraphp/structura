<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Asserts\ToNotHaveMethod;

/**
 * @mixin AbstractExpr
 */
trait MethodAssert
{
    public function toHaveMethod(string $name, string $message = ''): self
    {
        return $this->addExpr(new ToHaveMethod($name, $message));
    }

    public function toNotHaveMethod(string $name, string $message = ''): self
    {
        return $this->addExpr(new ToNotHaveMethod($name, $message));
    }

    public function toHaveConstructor(string $message = ''): self
    {
        return $this->toHaveMethod('__construct', $message);
    }

    public function toNotHaveConstructor(string $message = ''): self
    {
        return $this->toNotHaveMethod('__construct', $message);
    }

    public function toHaveDestructor(string $message = ''): self
    {
        return $this->toHaveMethod('__destruct', $message);
    }
}
