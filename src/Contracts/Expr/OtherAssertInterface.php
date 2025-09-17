<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

use Closure;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

interface OtherAssertInterface
{
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
    public function toHaveCorrespondingInterface(Closure $closure, string $message = ''): self;

    /**
     * @param Closure(ClassDescription): string $closure
     */
    public function toHaveCorrespondingTrait(Closure $closure, string $message = ''): self;

    public function toUseStrictTypes(string $message = ''): self;

    public function toUseDeclare(string $key, string $value, string $message = ''): self;

    /**
     * @param array<int,string>|string $patterns class names or regular expression patterns to
     *                                           be matched with namespaces
     */
    public function toBeInOneOfTheNamespaces(
        array|string $patterns,
        string $message = '',
    ): self;

    /**
     * @param array<int,string>|string $patterns class names or regular expression patterns not
     *                                           to be matched with namespaces
     */
    public function notToBeInOneOfTheNamespaces(
        array|string $patterns,
        string $message = '',
    ): self;
}
