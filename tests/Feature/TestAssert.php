<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\Tests\Fixture\Http\Controller\UserController;
use StructuraPhp\Structura\Tests\Fixture\Http\Resource\UserResource;

final class TestAssert extends TestBuilder
{
    #[TestDox('Http architecture rules')]
    public function testAssertArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir('tests/Fixture/Http')
            ->that($this->conditionThat(...))
            ->except($this->exception(...))
            ->should($this->conditionShould(...));
    }

    private function conditionThat(Expr $expr): void
    {
        $expr->toBeClasses();
    }

    private function conditionShould(Expr $expr): void
    {
        $expr
            ->toUseDeclare('strict_types', '1')
            ->toExtendsNothing()
            ->toNotUseTrait();
    }

    private function exception(Except $except): void
    {
        $except
            ->byClassname(UserResource::class, ToExtendNothing::class)
            // warning
            ->byClassname(UserController::class, ToUseDeclare::class);
    }
}
