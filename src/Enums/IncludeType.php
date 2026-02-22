<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Enums;

enum IncludeType: int
{
    case Include = 1;
    case IncludeOnce = 2;
    case Require = 3;
    case RequireOnce = 4;

    public function label(): string
    {
        return match ($this) {
            self::Include => 'include',
            self::IncludeOnce => 'include_once',
            self::Require => 'require',
            self::RequireOnce => 'require_once',
        };
    }
}
