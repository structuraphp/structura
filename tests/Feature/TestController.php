<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory;
use StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface;
use StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController;
use StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Tests\Fixture\Models\User;

final class TestController extends TestBuilder
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
                    ->toNotUseTrait()
                    ->toHaveSuffix('Controller')
                    ->toExtend(ControllerBase::class)
                    ->toHaveConstructor()
                    ->dependsOnlyOn([
                        RoleController::class,
                        User::class,
                    ])
                    ->dependsOnlyOnUseTrait([
                        HasFactory::class,
                    ])
                    ->dependsOnlyOnImplementation([
                        ShouldQueueInterface::class,
                    ]),
            );
    }
}
