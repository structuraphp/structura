<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\NotToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToHaveCorresponding;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingClass;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingEnum;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingInterface;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingTrait;

/**
 * @mixin AbstractExpr
 */
trait OtherAssert
{
    public function toHaveCorresponding(Closure $closure, string $message = ''): self
    {
        return $this->addExpr(new ToHaveCorresponding($closure, $message));
    }

    public function toHaveCorrespondingClass(Closure $closure, string $message = ''): self
    {
        return $this->addExpr(new ToHaveCorrespondingClass($closure, $message));
    }

    public function toHaveCorrespondingEnum(Closure $closure, string $message = ''): self
    {
        return $this->addExpr(new ToHaveCorrespondingEnum($closure, $message));
    }

    public function toHaveCorrespondingInterface(Closure $closure, string $message = ''): self
    {
        return $this->addExpr(new ToHaveCorrespondingInterface($closure, $message));
    }

    public function toHaveCorrespondingTrait(Closure $closure, string $message = ''): self
    {
        return $this->addExpr(new ToHaveCorrespondingTrait($closure, $message));
    }

    public function toBeInOneOfTheNamespaces(
        array|string $patterns,
        string $message = '',
    ): self {
        return $this->addExpr(new ToBeInOneOfTheNamespaces((array) $patterns, $message));
    }

    public function notToBeInOneOfTheNamespaces(
        array|string $patterns,
        string $message = '',
    ): self {
        return $this->addExpr(new NotToBeInOneOfTheNamespaces((array) $patterns, $message));
    }
}
