<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\ExprScript;

interface DeclareAssertInterface
{
    public function toUseStrictTypes(string $message = ''): self;

    public function toUseDeclare(string $key, string $value, string $message = ''): self;
}
