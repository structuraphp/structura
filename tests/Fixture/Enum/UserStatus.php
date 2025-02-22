<?php

declare(strict_types=1);

namespace Structura\Tests\Fixture\Enum;

enum UserStatus: int
{
    case Activate = 0;
    case InActivate = 1;
    case Blocked = 2;
}
