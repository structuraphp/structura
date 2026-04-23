<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Concerns\ExprScript\ThirdPartyAssert;
use StructuraPhp\Structura\Contracts\ExprScript\DependencyAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\ThirdPartyAssertInterface;

class ExprScript extends AbstractExpr implements ThirdPartyAssertInterface, DependencyAssertInterface
{
    use ThirdPartyAssert;
    use DependencyAssert;
}
