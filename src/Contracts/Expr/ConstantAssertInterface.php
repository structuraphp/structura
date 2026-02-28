<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

use StructuraPhp\Structura\Enums\VisibilityType;

interface ConstantAssertInterface
{
    public function toHaveConstant(VisibilityType $visibility, string $message = ''): self;

    public function toNotHaveConstant(VisibilityType $visibility, string $message = ''): self;

    public function toHavePublicConstant(string $message = ''): self;

    public function toHaveProtectedConstant(string $message = ''): self;

    public function toHavePrivateConstant(string $message = ''): self;

    public function toNotHavePublicConstant(string $message = ''): self;

    public function toNotHaveProtectedConstant(string $message = ''): self;

    public function toNotHavePrivateConstant(string $message = ''): self;
}
