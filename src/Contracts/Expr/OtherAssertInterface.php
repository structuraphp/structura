<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

use Closure;

interface OtherAssertInterface
{
    public function toHaveCorrespondingClass(Closure $closure, string $message = ''): self;

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
