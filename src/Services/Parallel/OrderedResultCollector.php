<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use Closure;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

/**
 * Buffers out of order worker results and releases them in test suite declaration order.
 *
 * Workers finish in whatever order their workload allows, but a result is only handed over once
 * every class declared before it has been handed over. That sliding window is what makes a
 * parallel run produce the exact same output as a sequential one.
 */
final class OrderedResultCollector
{
    /** @var array<string, int> */
    private array $indexByClass;

    /** @var array<int, AnalyseValueObject> results waiting for their predecessors, keyed by index */
    private array $pending = [];

    /** @var array<int, AnalyseValueObject> */
    private array $emitted = [];

    private int $nextToEmit = 0;

    /** Declaration index of the first class that tripped a stop-on threshold. */
    private ?int $stopIndex = null;

    /**
     * @param array<int, string> $classnames test classes, in declaration order
     * @param null|(Closure(AnalyseValueObject): void) $onEmit
     */
    public function __construct(
        private readonly array $classnames,
        private readonly ?Closure $onEmit = null,
    ) {
        $this->indexByClass = array_flip($classnames);
    }

    public function collect(string $classname, AnalyseValueObject $result, bool $stopOn): void
    {
        $index = $this->indexByClass[$classname]
            ?? throw new WorkerProtocolException(
                \sprintf('Worker returned a result for the unexpected class "%s".', $classname),
            );

        $this->pending[$index] = $result;

        if ($stopOn) {
            $this->stopIndex = $this->stopIndex === null
                ? $index
                : min($this->stopIndex, $index);
        }

        $this->flush();
    }

    /**
     * Releases every result whose predecessors are all in, never going past the class that
     * tripped a stop-on threshold.
     */
    public function flush(): void
    {
        $total = \count($this->classnames);

        while ($this->nextToEmit < $total) {
            if ($this->stopIndex !== null && $this->nextToEmit > $this->stopIndex) {
                return;
            }

            if (!isset($this->pending[$this->nextToEmit])) {
                return;
            }

            $result = $this->pending[$this->nextToEmit];
            unset($this->pending[$this->nextToEmit]);
            $this->nextToEmit++;

            $this->emitted[] = $result;

            if ($this->onEmit instanceof Closure) {
                ($this->onEmit)($result);
            }
        }
    }

    public function hasStopped(): bool
    {
        return $this->stopIndex !== null;
    }

    /**
     * @return array<int, AnalyseValueObject>
     */
    public function emitted(): array
    {
        return $this->emitted;
    }
}
