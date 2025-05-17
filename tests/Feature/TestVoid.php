<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

final class TestVoid extends TestBuilder
{
    #[TestDox('Asserts architecture rules')]
    public function testArchitecture(): void
    {
        $this
            ->allClasses()
            ->fromDir('src')
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
