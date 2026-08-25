<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Suite;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Benchmarks\Corpus;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

/**
 * Frozen suite: every rule passes on the corpus.
 *
 * Do not change the rules, it would invalidate the stored baselines.
 */
final class TestBenchPass extends TestBuilder
{
    #[TestDox('Passing architecture rules')]
    public function testPassingRules(): void
    {
        $this
            ->allClasses()
            ->fromDir(Corpus::dir())
            ->should($this->should(...));
    }

    private function should(Expr $expr): void
    {
        $expr
            ->toUseDeclare('strict_types', '1')
            ->toBeInOneOfTheNamespaces(['StructuraPhp\Structura\Benchmarks\Fixture\.+'])
            ->toNotDependsOn(patterns: ['Symfony\.+'])
            ->toNotDependsOnFunction(['dd', 'dump', 'var_dump'])
            ->toNotUseInclude();
    }
}
