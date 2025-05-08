<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use StructuraPhp\Structura\Builder\RuleBuilder;

interface ShouldInterface
{
    public function except(Closure $closure): ShouldInterface;

    public function should(Closure $closure): RuleBuilder;
}
