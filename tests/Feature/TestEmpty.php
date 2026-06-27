<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

final class TestEmpty extends TestBuilder
{
    #[TestDox('Void architecture rules')]
    public function testVoidArchitecture(): void
    {
        $this
            ->allClasses()
            ->fromDir('tests/Fixture/Void')
            ->that($this->that(...))
            ->except($this->except(...))
            ->should($this->should(...));
    }

    #[TestDox('Empty architecture rules')]
    public function testEmptyArchitecture(): void
    {
        $this
            ->allClasses()
            ->fromDir('tests/Fixture/Empty')
            ->that($this->that(...))
            ->except($this->except(...))
            ->should($this->should(...));
    }

    private function that(Expr $expr): void
    {
        // TODO: Implement or remove that() method.
    }

    private function should(Expr $expr): void
    {
        // TODO: Implement should() method.
    }

    private function except(Except $except): void
    {
        // TODO: implement or remove except() method.
    }
}
