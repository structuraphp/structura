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
}
