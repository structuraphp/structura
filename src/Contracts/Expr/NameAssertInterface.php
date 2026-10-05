<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface NameAssertInterface
{
    public function toHavePrefix(string $prefix, string $message = ''): self;

    public function toNotHavePrefix(string $prefix, string $message = ''): self;

    public function toHaveSuffix(string $suffix, string $message = ''): self;
}
