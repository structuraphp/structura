<?php

declare(strict_types=1);

namespace Structura\Contracts;

use Closure;
use Structura\Builder\RuleBuilder;

interface ShouldInterface
{
    public function except(Closure $closure): ShouldInterface;

    public function should(Closure $closure): RuleBuilder;
}
