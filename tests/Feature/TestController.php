<?php

declare(strict_types=1);

namespace Structura\Tests\Feature;

use Structura\Attributes\TestDox;
use Structura\Expr;
use Structura\Testing\TestBuilder;
use Structura\Tests\Fixture\Concerns\HasFactory;
use Structura\Tests\Fixture\Contract\ShouldQueueInterface;
use Structura\Tests\Fixture\Http\Controller\RoleController;
use Structura\Tests\Fixture\Http\ControllerBase;
use Structura\Tests\Fixture\Models\User;

class TestController extends TestBuilder
{
    #[TestDox('Controllers architecture rules')]
    public function testControllersArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir('tests/Fixture/Http/Controller')
            ->should(
                static fn (Expr $expr): Expr => $expr
                    ->toBeClasses()
                    ->toUseDeclare('strict_types', '1')
                    ->toUseNothing()
                    ->toHaveSuffix('Controller')
                    ->toExtend(ControllerBase::class)
                    ->toHaveConstructor()
                    ->dependsOnlyOn([
                        HasFactory::class,
                        RoleController::class,
                        ShouldQueueInterface::class,
                        User::class,
                    ]),
            );
    }
}
