<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Enums;

enum DependenciesType
{
    case All;
    case Interfaces;
    case Extends;
    case Traits;
    case Attributes;
}
