<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use StructuraPhp\Structura\ValueObjects\ClassDescription;

interface ExceptInterface
{
    public function except(ExceptInterface $expr, ClassDescription $description): bool;
}
