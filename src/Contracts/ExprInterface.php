<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Stringable;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

interface ExprInterface extends Stringable
{
    public function assert(ClassDescription $class): bool;

    public function getViolation(ClassDescription $class): ViolationValueObject;
}
