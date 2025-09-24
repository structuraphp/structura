<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface NameAssertInterface
{
    public function toHavePrefix(string $prefix): self;

    public function toHaveSuffix(string $suffix, string $message = ''): self;
}
