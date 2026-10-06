<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Asserts\ToBeAbstract;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToNotDependOn;
use StructuraPhp\Structura\Asserts\ToOnlyDependOn;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

final class TestAssert extends TestBuilder
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
            ->toNotDependOn([
                ClassDescription::class,
            ])
            ->toHaveMethod('__toString')
            ->toUseDeclare('strict_types', '1')
            ->toHavePrefix('To')
            ->toExtendNothing()
            ->toNotUseTrait()
            ->toHaveConstructor();
    }

    private function exception(Except $except): void
    {
        $except
            ->byClassname(ToBeAbstract::class, ToNotDependOn::class)
            ->byClassname(ToOnlyDependOn::class, ToHavePrefix::class)
            // warning
            ->byClassname(ToBeReadonly::class, ToHavePrefix::class);
    }
}
