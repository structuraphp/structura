<?php

declare(strict_types=1);

namespace Structura\Enums;

enum ClassType
{
    case AnonymousClass_;
    case Class_;
    case Enum_;
    case Interface_;
    case Trait_;
}
