<?php

declare(strict_types=1);

namespace Structura\Tests\Fixture\Http\Controller;

use Structura\Tests\Fixture\Http\ControllerBase;

class UserController extends ControllerBase
{
    public int $bar = 1;

    public function __invoke(): void {}

    public function __toString(): string
    {
        return RoleController::class;
    }
}
