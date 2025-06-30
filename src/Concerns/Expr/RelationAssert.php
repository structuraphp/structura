<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use StructuraPhp\Structura\Asserts\ToExtend;
use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Asserts\ToHaveAttribute;
use StructuraPhp\Structura\Asserts\ToHaveNoAttribute;
use StructuraPhp\Structura\Asserts\ToHaveOnlyAttribute;
use StructuraPhp\Structura\Asserts\ToImplement;
use StructuraPhp\Structura\Asserts\ToImplementNothing;
use StructuraPhp\Structura\Asserts\ToNotUseTrait;
use StructuraPhp\Structura\Asserts\ToOnlyImplement;
use StructuraPhp\Structura\Asserts\ToOnlyUseTrait;
use StructuraPhp\Structura\Asserts\ToUseTrait;
use StructuraPhp\Structura\Expr;

/**
 * @mixin Expr
 */
trait RelationAssert
{
    public function toExtend(string $name, string $message = ''): self
    {
        $this->extendDependencies[] = [$name];

        return $this->addExpr(new ToExtend($name, $message));
    }

    public function toExtendsNothing(string $message = ''): self
    {
        return $this->addExpr(new ToExtendNothing($message));
    }

    public function toImplement(array|string $names, string $message = ''): self
    {
        $this->implementDependencies[] = (array) $names;

        return $this->addExpr(new ToImplement($names, $message));
    }

    public function toImplementNothing(string $message = ''): self
    {
        return $this->addExpr(new ToImplementNothing($message));
    }

    public function toOnlyImplement(string $name, string $message = ''): self
    {
        $this->implementDependencies[] = [$name];

        return $this->addExpr(new ToOnlyImplement($name, $message));
    }

    public function toUseTrait(array|string $names, string $message = ''): self
    {
        $this->traitDependencies[] = (array) $names;

        return $this->addExpr(new ToUseTrait($names, $message));
    }

    public function toNotUseTrait(string $message = ''): self
    {
        return $this->addExpr(new ToNotUseTrait($message));
    }

    public function toOnlyUseTrait(string $name, string $message = ''): self
    {
        $this->traitDependencies[] = [$name];

        return $this->addExpr(new ToOnlyUseTrait($name, $message));
    }

    public function toHaveAttribute(string $name, string $message = ''): self
    {
        $this->attributDependencies[] = [$name];

        return $this->addExpr(new ToHaveAttribute($name, $message));
    }

    public function toHaveNoAttribute(string $message = ''): self
    {
        return $this->addExpr(new ToHaveNoAttribute($message));
    }

    public function toHaveOnlyAttribute(string $name, string $message = ''): self
    {
        $this->attributDependencies[] = [$name];

        return $this->addExpr(new ToHaveOnlyAttribute($name, $message));
    }
}
