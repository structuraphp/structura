<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Enums;

enum VisibilityType: string
{
    case Public = 'public';
    case Protected = 'protected';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'public',
            self::Protected => 'protected',
            self::Private => 'private',
        };
    }
}
