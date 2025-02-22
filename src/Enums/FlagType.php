<?php

declare(strict_types=1);

namespace Structura\Enums;

use PhpParser\Modifiers;

enum FlagType: int
{
    case ModifierPublic = Modifiers::PUBLIC;
    case ModifierProtected = Modifiers::PROTECTED;
    case ModifierPrivate = Modifiers::PRIVATE;
    case ModifierStatic = Modifiers::STATIC;
    case ModifierAbstract = Modifiers::ABSTRACT;
    case ModifierFinal = Modifiers::FINAL;
    case ModifierReadonly = Modifiers::READONLY;
}
