<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Asserts\ToBeAbstract;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
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
            ->except(
                ToBeAbstract::class,
                static fn (Except $except): Except => $except->toNotDependsOn(),
            )
            ->except(
                [DependsOnlyOn::class, ToBeReadonly::class],
                static fn (Except $except): Except => $except->byAssert(ToHavePrefix::class),
            )
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
}
