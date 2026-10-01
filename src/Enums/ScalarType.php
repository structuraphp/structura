<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Enums;

enum ScalarType: string
{
    case Int = 'int';
    case String = 'string';

    public function label(): string
    {
        return match ($this) {
            self::Int => 'int',
            self::String => 'string',
        };
    }
}
