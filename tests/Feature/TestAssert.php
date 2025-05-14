<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Asserts\ToBeAbstract;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToNotDependsOn;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

class TestAssert extends TestBuilder
{
    #[TestDox('Asserts architecture rules')]
    public function testAssertArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir('src/Asserts')
            ->that($this->conditionThat(...))
            ->except($this->exception(...))
            ->should($this->conditionShould(...));
    }

    private function conditionThat(Expr $expr): void
    {
        $expr->toImplement(ExprInterface::class);
    }

    private function conditionShould(Expr $expr): void
    {
        $expr
            ->toBeClasses()
            ->toNotDependsOn([
                ClassDescription::class,
            ])
            ->toHaveMethod('__toString')
            ->toUseDeclare('strict_types', '1')
            ->toHavePrefix('To')
            ->toExtendsNothing()
            ->toNotUseTrait()
            ->toHaveConstructor();
    }

    private function exception(Except $except): void
    {
        $except
            ->byClassname(ToBeAbstract::class, ToNotDependsOn::class)
            ->byClassname(DependsOnlyOn::class, ToHavePrefix::class)
            // warning
            ->byClassname(ToBeReadonly::class, ToHavePrefix::class);
    }
}
