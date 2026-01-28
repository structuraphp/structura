<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Testing\TestBuilder;

final class TestConfig extends TestBuilder
{
    #[TestDox('Binary architecture rules')]
    public function testAssertArchitectureRules(): void
    {
        $this
            ->allScripts()
            ->fromDir('tests/Fixture/Helper')
            ->should($this->conditionShould(...));
    }

    private function conditionShould(ExprScript $expr): void
    {
        $expr
            ->toUseStrictTypes();
    }
}
