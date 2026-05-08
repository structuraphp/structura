<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

use Closure;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

interface CorrespondingAssertInterface
{
    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorresponding(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorrespondingClass(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorrespondingEnum(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorrespondingFile(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toNotHaveCorrespondingFile(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorrespondingInterface(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorrespondingTrait(Closure $closure, string $message = ''): self;
}
