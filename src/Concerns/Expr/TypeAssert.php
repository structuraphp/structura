<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use Attribute;
use StructuraPhp\Structura\Asserts\ToBeAbstract;
use StructuraPhp\Structura\Asserts\ToBeAnonymousClasses;
use StructuraPhp\Structura\Asserts\ToBeAttribute;
use StructuraPhp\Structura\Asserts\ToBeBackedEnums;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\Asserts\ToBeEnums;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Asserts\ToBeInterfaces;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Asserts\ToBeTraits;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprIteratorAggregate;
use StructuraPhp\Structura\Enums\ScalarType;

/**
 * @mixin ExprIteratorAggregate<ExprInterface>
 */
trait TypeAssert
{
    public function toBeAbstract(string $message = ''): self
    {
        return $this->addExpr(new ToBeAbstract($message));
    }

    public function toBeAnonymousClasses(string $message = ''): self
    {
        return $this->addExpr(new ToBeAnonymousClasses($message));
    }

    public function toBeClasses(string $message = ''): self
    {
        return $this->addExpr(new ToBeClasses($message));
    }

    public function toBeEnums(string $message = ''): self
    {
        return $this->addExpr(new ToBeEnums($message));
    }

    public function toBeBackedEnums(?ScalarType $scalarType = null, string $message = ''): self
    {
        return $this->addExpr(new ToBeBackedEnums($scalarType, $message));
    }

    public function toBeFinal(string $message = ''): self
    {
        return $this->addExpr(new ToBeFinal($message));
    }

    public function toBeInterfaces(string $message = ''): self
    {
        return $this->addExpr(new ToBeInterfaces($message));
    }

    public function toBeInvokable(string $message = ''): self
    {
        return $this->toHaveMethod('__invoke', $message);
    }

    public function toBeReadonly(string $message = ''): self
    {
        return $this->addExpr(new ToBeReadonly($message));
    }

    public function toBeTraits(string $message = ''): self
    {
        return $this->addExpr(new ToBeTraits($message));
    }

    public function toBeAttribute(int $flag = Attribute::TARGET_ALL, string $message = ''): self
    {
        return $this->addExpr(new ToBeAttribute($flag, $message));
    }
}
