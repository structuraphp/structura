<?php

declare(strict_types=1);

namespace Structura\Contracts;

use Stringable;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

interface ExprInterface extends Stringable
{
    public function assert(ClassDescription $class): bool;

    public function getViolation(ClassDescription $class): ViolationValueObject;
}
