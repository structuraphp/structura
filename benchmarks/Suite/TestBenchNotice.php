<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Suite;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Benchmarks\Corpus;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

/**
 * Frozen suite: the directory does not exist, the analysis emits a notice.
 *
 * Do not change the rules, it would invalidate the stored baselines.
 */
final class TestBenchNotice extends TestBuilder
{
    #[TestDox('Missing directory rules')]
    public function testMissingDirectory(): void
    {
        $this
            ->allClasses()
            ->fromDir(Corpus::dir() . '/DoesNotExist')
            ->should($this->should(...));
    }

    private function should(Expr $expr): void
    {
        $expr->toBeFinal();
    }
}
