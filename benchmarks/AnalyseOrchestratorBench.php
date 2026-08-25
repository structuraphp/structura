<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Services\AnalyseOrchestrator;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;

/**
 * Measures the analysis orchestration: test class discovery, reflection,
 * assertion execution, event dispatching and result merging.
 *
 * The test classes live in Suite/ and are frozen like the corpus.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(5)]
#[Iterations(5)]
final class AnalyseOrchestratorBench
{
    private FinderService $finderService;

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
    }

    public function benchRun(): void
    {
        (new AnalyseOrchestrator())->run($this->finderService);
    }

    public function benchRunWithFilter(): void
    {
        (new AnalyseOrchestrator(filter: 'violation'))->run($this->finderService);
    }

    public function benchRunStopOnError(): void
    {
        try {
            (new AnalyseOrchestrator(stopOnError: true))->run($this->finderService);
        } catch (StopOnException) {
            // expected: the run stops on the first violating test class
        }
    }
}
