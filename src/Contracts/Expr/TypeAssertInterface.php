<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

use Attribute;
use StructuraPhp\Structura\Enums\ScalarType;

interface TypeAssertInterface
{
    public function toBeAbstract(string $message = ''): self;

    public function toBeAnonymousClasses(string $message = ''): self;

    public function toBeClasses(string $message = ''): self;

    public function toBeEnums(string $message = ''): self;

    public function toBeBackedEnums(?ScalarType $scalarType = null, string $message = ''): self;

    public function toBeFinal(string $message = ''): self;

    public function toBeInterfaces(string $message = ''): self;

    public function toBeInvokable(string $message = ''): self;

    public function toBeReadonly(string $message = ''): self;

    public function toBeTraits(string $message = ''): self;

    /**
     * @param int-mask-of<Attribute::IS_REPEATABLE|Attribute::TARGET_*> $flag
     */
    public function toBeAttribute(int $flag = Attribute::TARGET_ALL, string $message = ''): self;
}
