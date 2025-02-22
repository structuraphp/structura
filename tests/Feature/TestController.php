<?php

declare(strict_types=1);

namespace Structura\Tests\Feature;

use Structura\Attributes\TestDox;
use Structura\Expr;
use Structura\Testing\TestBuilder;
use Structura\Tests\Fixture\Http\ControllerBase;

class TestController extends TestBuilder
{
    #[TestDox('Controllers architecture rules')]
    public function testControllersArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir('tests/Fixture/Http/Controller')
            ->should(
                static fn(Expr $expr): Expr => $expr
                    ->toBeClasses()
                    ->toUseDeclare('strict_types', '1')
                    ->toUseNothing()
                    ->toHaveSuffix('Controller')
                    ->toExtend(ControllerBase::class)
                    ->toHaveConstructor(),
            );
    }
}
