<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\Services\Parallel\WorkerMessageReader;

/**
 * Pins down the parent side of the NDJSON protocol.
 *
 * These are the paths that run when a worker misbehaves, and they used to sit inside WorkerPool
 * where reaching them meant orchestrating a real process. Here they are plain function calls, so
 * every refusal the parent can produce is covered.
 */
#[CoversClass(WorkerMessageReader::class)]
final class WorkerMessageReaderTest extends TestCase
{
    /**
     * A pipe carries bytes, not lines: losing the tail of a chunk would corrupt the next message.
     */
    public function testAPartialLineIsHeldUntilItsNewlineArrives(): void
    {
        $reader = new WorkerMessageReader();

        self::assertSame([], $reader->push('{"type":"result","cla'));
        self::assertSame(
            ['{"type":"result","class":"C1"}'],
            $reader->push('ss":"C1"}' . "\n"),
        );
    }

    public function testSeveralLinesInOneChunkComeBackInOrder(): void
    {
        $reader = new WorkerMessageReader();

        self::assertSame(
            ['{"class":"A"}', '{"class":"B"}'],
            $reader->push('{"class":"A"}' . "\n" . '{"class":"B"}' . "\n"),
        );
    }

    public function testAChunkEndingOnANewlineLeavesNothingBuffered(): void
    {
        $reader = new WorkerMessageReader();
        $reader->push('{"class":"A"}' . "\n");

        self::assertSame([], $reader->push(''));
    }

    public function testTrailingBytesStayBufferedAcrossSeveralEmptyReads(): void
    {
        $reader = new WorkerMessageReader();
        $reader->push('{"class":"A"}');

        self::assertSame([], $reader->push(''));
        self::assertSame(['{"class":"A"}'], $reader->push("\n"));
    }

    #[TestWith(["\n\n\n"])]
    #[TestWith(["   \n\t\n"])]
    #[TestWith([''])]
    public function testBlankLinesAreDropped(string $chunk): void
    {
        self::assertSame([], (new WorkerMessageReader())->push($chunk));
    }

    #[TestWith(['not json'])]
    #[TestWith(['123'])]
    #[TestWith(['"a string"'])]
    #[TestWith(['{"unterminated": '])]
    public function testANonArrayLineIsRefused(string $line): void
    {
        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Unreadable worker output: ' . $line);

        (new WorkerMessageReader())->decode($line);
    }

    public function testAWorkerSideErrorBecomesAnException(): void
    {
        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Worker failed on "App\Tests\TestA": Undefined array key "foo"');

        (new WorkerMessageReader())->decode(
            '{"type":"error","class":"App\\\Tests\\\TestA","message":"Undefined array key \"foo\""}',
        );
    }

    /**
     * An error line is itself worker output, so it cannot be trusted to carry its own fields.
     */
    public function testAnErrorWithoutClassOrMessageFallsBackToPlaceholders(): void
    {
        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Worker failed on "unknown": unknown error');

        (new WorkerMessageReader())->decode('{"type":"error","class":42,"message":[]}');
    }

    /**
     * A well formed JSON object missing a field is the dangerous case: it would otherwise be
     * merged into the totals as a silent zero.
     */
    #[TestWith(['{"type":"result","class":"C1"}'])]
    #[TestWith(['{"type":"result","data":{"countPass":6}}'])]
    #[TestWith(['{"class":"C1","data":"not an array"}'])]
    #[TestWith(['{"class":42,"data":{}}'])]
    #[TestWith(['{}'])]
    #[TestWith(['[]'])]
    public function testAResultMissingClassOrDataIsRefused(string $line): void
    {
        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Incomplete worker result: ' . $line);

        (new WorkerMessageReader())->decode($line);
    }

    public function testACompleteResultIsProjectedOntoTheMessage(): void
    {
        $message = (new WorkerMessageReader())->decode(
            '{"type":"result","class":"App\\\Tests\\\TestA","stopOn":true,"data":{"countPass":6}}',
        );

        self::assertSame('App\Tests\TestA', $message->classname);
        self::assertSame(['countPass' => 6], $message->data);
        self::assertTrue($message->stopOn);
    }

    /**
     * stopOn drives the drain, so anything that is not a literal true must not trip it.
     */
    #[TestWith(['{"class":"C1","data":{}}'])]
    #[TestWith(['{"class":"C1","data":{},"stopOn":false}'])]
    #[TestWith(['{"class":"C1","data":{},"stopOn":"true"}'])]
    #[TestWith(['{"class":"C1","data":{},"stopOn":1}'])]
    #[TestWith(['{"class":"C1","data":{},"stopOn":null}'])]
    public function testStopOnIsOnlyTrueForALiteralTrue(string $line): void
    {
        self::assertFalse((new WorkerMessageReader())->decode($line)->stopOn);
    }
}
