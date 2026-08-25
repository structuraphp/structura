<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Model;
use StructuraPhp\Structura\Builder\AllClasses;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Services\ExecuteService;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Measures a whole architecture test: finder, parser, that/except filtering,
 * assertions and event dispatching.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(5)]
#[Iterations(5)]
final class ExecuteServiceBench
{
    private RuleValuesObject $simpleRule;

    private RuleValuesObject $filteredRule;

    public function setUp(): void
    {
        $this->simpleRule = AllClasses::allClasses()
            ->fromDir(Corpus::dir())
            ->should(static function (Expr $expr): void {
                $expr
                    ->toUseDeclare('strict_types', '1')
                    ->toBeInOneOfTheNamespaces(['StructuraPhp\Structura\Benchmarks\Fixture\.+'])
                    ->toNotDependsOn(patterns: ['Symfony\.+'])
                    ->toNotDependsOnFunction(['dd', 'dump', 'var_dump']);
            })
            ->getRuleObject();

        $this->filteredRule = AllClasses::allClasses()
            ->fromDir(Corpus::dir())
            ->that(static function (Expr $expr): void {
                $expr->toExtend(Model::class);
            })
            ->except(static function (Except $except): void {
                $except->byClassname(Model::class, ToBeFinal::class);
            })
            ->should(static function (Expr $expr): void {
                $expr
                    ->toBeFinal()
                    ->toHaveMethod('id')
                    ->toImplementNothing();
            })
            ->getRuleObject();
    }

    public function benchExecute(): void
    {
        (new ExecuteService(new EventDispatcher(), $this->simpleRule))->assert();
    }

    public function benchExecuteWithThatAndExcept(): void
    {
        (new ExecuteService(new EventDispatcher(), $this->filteredRule))->assert();
    }
}
