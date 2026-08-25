<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Suite;

use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Benchmarks\Corpus;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Order;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

/**
 * Frozen suite: produces violations, plus a warning through an exception
 * that no longer applies (Order is final, so ToBeFinal passes for it).
 *
 * Do not change the rules, it would invalidate the stored baselines.
 */
final class TestBenchViolation extends TestBuilder
{
    #[TestDox('Violating architecture rules')]
    public function testViolatingRules(): void
    {
        $this
            ->allClasses()
            ->fromDir(Corpus::dir())
            ->that($this->that(...))
            ->except($this->except(...))
            ->should($this->should(...));
    }

    private function that(Expr $expr): void
    {
        $expr->toBeClasses();
    }

    private function should(Expr $expr): void
    {
        $expr
            ->toBeFinal()
            ->toHaveSuffix('Dto')
            ->toHaveMethod('__invoke');
    }

    private function except(Except $except): void
    {
        $except->byClassname(Order::class, ToBeFinal::class);
    }
}
