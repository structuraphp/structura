<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\Services\Parallel\OrderedResultCollector;
use StructuraPhp\Structura\Tests\Helper\AnalyseResultRecorder;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

#[CoversClass(OrderedResultCollector::class)]
final class OrderedResultCollectorTest extends TestCase
{
    /**
     * Workers report in whatever order they finish; the collector is what restores determinism.
     */
    public function testResultsAreReleasedInDeclarationOrder(): void
    {
        $recorder = new AnalyseResultRecorder();
        $collector = new OrderedResultCollector(['A', 'B', 'C'], $recorder->record(...));

        $collector->collect('C', $this->analyseResult(3), false);
        self::assertSame([], $recorder->passCounts(), 'C must wait for A and B');

        $collector->collect('B', $this->analyseResult(2), false);
        self::assertSame([], $recorder->passCounts(), 'B must still wait for A');

        $collector->collect('A', $this->analyseResult(1), false);
        self::assertSame([1, 2, 3], $recorder->passCounts());
    }

    public function testAlreadyOrderedResultsAreReleasedImmediately(): void
    {
        $recorder = new AnalyseResultRecorder();
        $collector = new OrderedResultCollector(['A', 'B'], $recorder->record(...));

        $collector->collect('A', $this->analyseResult(1), false);
        self::assertSame([1], $recorder->passCounts());

        $collector->collect('B', $this->analyseResult(2), false);
        self::assertSame([1, 2], $recorder->passCounts());
    }

    /**
     * A late class tripping the threshold must not truncate the classes declared before it.
     */
    public function testStopOnTruncatesAfterTheTrippingClass(): void
    {
        $collector = new OrderedResultCollector(['A', 'B', 'C', 'D']);

        $collector->collect('D', $this->analyseResult(4), false);
        $collector->collect('B', $this->analyseResult(2), true);
        $collector->collect('A', $this->analyseResult(1), false);
        $collector->collect('C', $this->analyseResult(3), false);
        $collector->flush();

        self::assertTrue($collector->hasStopped());
        self::assertSame([1, 2], array_map(
            static fn (AnalyseValueObject $result): int => $result->countPass,
            $collector->emitted(),
        ));
    }

    public function testEarliestTrippingClassWins(): void
    {
        $collector = new OrderedResultCollector(['A', 'B', 'C']);

        $collector->collect('C', $this->analyseResult(3), true);
        $collector->collect('B', $this->analyseResult(2), true);
        $collector->collect('A', $this->analyseResult(1), false);
        $collector->flush();

        self::assertSame([1, 2], array_map(
            static fn (AnalyseValueObject $result): int => $result->countPass,
            $collector->emitted(),
        ));
    }

    public function testUnexpectedClassIsRejected(): void
    {
        $this->expectException(WorkerProtocolException::class);

        (new OrderedResultCollector(['A']))->collect('Z', $this->analyseResult(1), false);
    }

    private function analyseResult(int $countPass): AnalyseValueObject
    {
        return new AnalyseValueObject(0.0, $countPass, 0, 0, 0, []);
    }
}
