<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\ExprScript;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToHaveAnonymousClass;
use StructuraPhp\Structura\Asserts\ToHaveFilePermission;
use StructuraPhp\Structura\Asserts\ToNotHaveAnonymousClass;
use StructuraPhp\Structura\Asserts\ToNotUseInclude;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Asserts\ToUseInclude;
use StructuraPhp\Structura\Enums\IncludeType;

/**
 * @mixin AbstractExpr
 */
trait ThirdPartyAssert
{
    public function toUseStrictTypes(string $message = ''): self
    {
        return $this->toUseDeclare('strict_types', '1', $message);
    }

    public function toUseDeclare(string $key, string $value, string $message = ''): self
    {
        return $this->addExpr(new ToUseDeclare($key, $value, $message));
    }

    public function toUseInclude(IncludeType $includeType, string $message = ''): self
    {
        return $this->addExpr(new ToUseInclude($includeType, $message));
    }

    public function toNotUseInclude(string $message = ''): self
    {
        return $this->addExpr(new ToNotUseInclude($message));
    }

    public function toHaveAnonymousClass(string $message = ''): self
    {
        return $this->addExpr(new ToHaveAnonymousClass($message));
    }

    public function toNotHaveAnonymousClass(string $message = ''): self
    {
        return $this->addExpr(new ToNotHaveAnonymousClass($message));
    }

    public function toHaveFilePermission(string $expectedPermission, string $message = ''): self
    {
        return $this->addExpr(new ToHaveFilePermission($expectedPermission, $message));
    }
}
