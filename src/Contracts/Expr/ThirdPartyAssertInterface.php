<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\Expr;

interface ThirdPartyAssertInterface
{
    /**
     * @param array<int,string>|string $patterns class names or regular expression patterns to
     *                                           be matched with namespaces
     */
    public function toBeInNamespaces(
        array|string $patterns,
        string $message = '',
    ): self;

    /**
     * @param array<int,string>|string $patterns class names or regular expression patterns not
     *                                           to be matched with namespaces
     */
    public function toNotBeInNamespaces(
        array|string $patterns,
        string $message = '',
    ): self;
}
