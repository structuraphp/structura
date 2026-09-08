<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use Generator;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Console\Enums\CommonOption;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Services\Parallel\ParallelAnalyseOrchestrator;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;

/**
 * Measures the parallel orchestration against the same frozen suite as AnalyseOrchestratorBench.
 *
 * Parallelism trades a fixed cost -- spawning processes, reloading the configuration and the test
 * files once per worker -- against analysing several test classes at once. On this deliberately
 * small suite the fixed cost dominates, so these numbers are there to track that overhead rather
 * than to show a speed-up; the win only appears on suites large enough to amortise it.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(2)]
#[Iterations(3)]
final class ParallelAnalyseOrchestratorBench
{
    private FinderService $finderService;

    /** @var array<int, string> */
    private array $workerOptions;

    public function setUp(): void
    {
        $configValueObject = new ConfigValueObject(
            testSuites: ['bench' => __DIR__ . '/Suite'],
            rootNamespace: null,
            errorFormatter: [],
            progressFormatter: [],
            extensions: [],
            autoload: null,
        );

        $this->finderService = new FinderService($configValueObject, 'bench');
        $this->workerOptions = [
            '--' . CommonOption::Config->value . '=' . __DIR__ . '/structura.php',
            '--testsuite=bench',
        ];
    }

    /**
     * @return Generator<string, array<string, int>>
     */
    public function provideProcesses(): Generator
    {
        yield '2 processes' => ['processes' => 2];

        yield '4 processes' => ['processes' => 4];
    }

    /**
     * @param array<string, int> $params
     */
    #[ParamProviders('provideProcesses')]
    public function benchRun(array $params): void
    {
        (new ParallelAnalyseOrchestrator($params['processes'], $this->workerOptions))
            ->run($this->finderService);
    }
}
