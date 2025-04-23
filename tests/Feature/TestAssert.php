<?php

declare(strict_types=1);

namespace Structura\Tests\Feature;

use Structura\Asserts\DependsOnlyOn;
use Structura\Asserts\ToBeAbstract;
use Structura\Asserts\ToHavePrefix;
use Structura\Asserts\ToNotDependsOn;
use Structura\Attributes\TestDox;
use Structura\Contracts\ExprInterface;
use Structura\Except;
use Structura\Expr;
use Structura\Testing\TestBuilder;
use Structura\ValueObjects\ClassDescription;

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
            ->toUseNothing()
            ->toHaveConstructor();
    }

    private function exception(Except $except): void
    {
        $except
            ->byRule(ToBeAbstract::class, ToNotDependsOn::class)
            ->byRule(DependsOnlyOn::class, ToHavePrefix::class)
        ;
    }
}
