<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToHaveConstant;
use StructuraPhp\Structura\Asserts\ToNotHaveConstant;
use StructuraPhp\Structura\Enums\VisibilityType;

/**
 * @mixin AbstractExpr
 */
trait ConstantAssert
{
    public function toHaveConstant(VisibilityType $visibility, string $message = ''): self
    {
        return $this->addExpr(new ToHaveConstant($visibility, $message));
    }

    public function toNotHaveConstant(VisibilityType $visibility, string $message = ''): self
    {
        return $this->addExpr(new ToNotHaveConstant($visibility, $message));
    }

    public function toHavePublicConstant(string $message = ''): self
    {
        return $this->toHaveConstant(VisibilityType::Public, $message);
    }

    public function toHaveProtectedConstant(string $message = ''): self
    {
        return $this->toHaveConstant(VisibilityType::Protected, $message);
    }

    public function toHavePrivateConstant(string $message = ''): self
    {
        return $this->toHaveConstant(VisibilityType::Private, $message);
    }

    public function toNotHavePublicConstant(string $message = ''): self
    {
        return $this->toNotHaveConstant(VisibilityType::Public, $message);
    }

    public function toNotHaveProtectedConstant(string $message = ''): self
    {
        return $this->toNotHaveConstant(VisibilityType::Protected, $message);
    }

    public function toNotHavePrivateConstant(string $message = ''): self
    {
        return $this->toNotHaveConstant(VisibilityType::Private, $message);
    }
}
