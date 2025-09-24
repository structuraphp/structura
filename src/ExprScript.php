<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use StructuraPhp\Structura\Concerns\ExprScript\DeclareAssert;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Contracts\ExprScript\DeclareAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\DependencyAssertInterface;

class ExprScript extends AbstractExpr implements DeclareAssertInterface, DependencyAssertInterface
{
    use DeclareAssert;
    use DependencyAssert;
}
