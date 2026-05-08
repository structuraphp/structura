<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

interface PathResolverAwareInterface
{
    /**
     * Injects the map of custom function names to their resolved paths.
     * These are used by assertions that need to resolve include/require paths
     * from function calls found in the AST (e.g. base_path(), app_path()…).
     *
     * @param array<string, string> $pathResolvers
     */
    public function setPathResolvers(array $pathResolvers): void;
}
