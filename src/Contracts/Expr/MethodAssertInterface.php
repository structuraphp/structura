<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface MethodAssertInterface
{
    public function toHaveMethod(string $name, string $message = ''): self;

    public function toHaveConstructor(string $message = ''): self;

    public function toHaveDestructor(string $message = ''): self;
}
