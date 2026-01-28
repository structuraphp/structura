<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Enums;

enum ClassType
{
    case AnonymousClass_;
    case Class_;
    case Enum_;
    case Interface_;
    case Trait_;

    public function label(): string
    {
        return match ($this) {
            self::AnonymousClass_ => 'an anonymous class',
            self::Class_ => 'an class',
            self::Enum_ => 'an enum',
            self::Interface_ => 'an interface',
            self::Trait_ => 'a trait',
        };
    }
}
