<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Fixture\Http\Controller;

use StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase;

class UserController extends ControllerBase
{
    public int $bar = 1;

    public function __invoke(): void {}

    public function __toString(): string
    {
        return RoleController::class;
    }
}
