<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface RelationAssertInterface
{
    /**
     * @param class-string $name
     */
    public function toExtend(string $name, string $message = ''): self;

    public function toExtendsNothing(string $message = ''): self;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toImplement(array|string $names, string $message = ''): self;

    public function toImplementNothing(string $message = ''): self;

    /**
     * @param class-string $name
     */
    public function toOnlyImplement(string $name, string $message = ''): self;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function toUseTrait(array|string $names, string $message = ''): self;

    public function toNotUseTrait(string $message = ''): self;

    /**
     * @param class-string $name
     */
    public function toOnlyUseTrait(string $name, string $message = ''): self;

    /**
     * @param class-string $name
     */
    public function toHaveAttribute(string $name, string $message = ''): self;

    public function toHaveNoAttribute(string $message = ''): self;

    /**
     * @param class-string $name
     */
    public function toHaveOnlyAttribute(string $name, string $message = ''): self;
}
