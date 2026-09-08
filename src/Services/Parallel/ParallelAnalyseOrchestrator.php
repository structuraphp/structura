<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use Closure;
use StructuraPhp\Structura\Contracts\AnalyseOrchestratorInterface;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

/**
 * Analyses the test suite across several worker processes.
 *
 * Test classes are handed out one at a time to a pool of persistent workers, but results are
 * released back to the caller strictly in test suite declaration order: a result is only emitted
 * once every class declared before it has been emitted. Parallel runs therefore produce output
 * that is byte for byte identical to sequential ones.
 *
 * The same holds for the stop-on-* thresholds. When a worker trips one, the pool stops handing
 * out new work but drains what is already in flight, and results are then truncated after the
 * first tripping class in declaration order -- exactly where the sequential orchestrator stops.
 */
final readonly class ParallelAnalyseOrchestrator implements AnalyseOrchestratorInterface
{
    /**
     * @param array<int, string> $workerOptions CLI options forwarded to every worker so that it
     *                                          analyses exactly what the parent was asked to
     */
    public function __construct(
        private int $processes,
        private array $workerOptions = [],
        private ?AnalyseResultSerializer $serializer = null,
    ) {}

    public function run(FinderService $finder, ?Closure $onClassAnalysed = null): AnalyseValueObject
    {
        $timeStart = microtime(true);
        $classnames = array_values($finder->getClassTests());

        if ($classnames === []) {
            return AnalyseValueObject::merge($timeStart);
        }

        $serializer = $this->serializer ?? new AnalyseResultSerializer();
        $collector = new OrderedResultCollector($classnames, $onClassAnalysed);

        $pool = new WorkerPool($this->processes, $this->workerOptions);
        $pool->run(
            $classnames,
            /** @param array<array-key, mixed> $data */
            static function (string $classname, array $data, bool $workerStoppedOn) use (
                $serializer,
                $timeStart,
                $collector,
            ): bool {
                $collector->collect(
                    $classname,
                    $serializer->fromArray($data, $timeStart),
                    $workerStoppedOn,
                );

                return !$collector->hasStopped();
            },
        );

        // Release whatever landed while the pool was draining.
        $collector->flush();

        if ($collector->hasStopped()) {
            throw new StopOnException(
                AnalyseValueObject::merge($timeStart, ...$collector->emitted()),
            );
        }

        return AnalyseValueObject::merge($timeStart, ...$collector->emitted());
    }
}
