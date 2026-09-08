<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\Services\Parallel\ComposerAutoloadLocator;
use StructuraPhp\Structura\Services\Parallel\WorkerCommandLine;
use StructuraPhp\Structura\Services\Parallel\WorkerPool;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Drives a real pool against stub workers rather than against real analysers.
 *
 * The pool's job is to keep processes fed, reassemble their output and notice when they die --
 * none of which needs an analysis to happen. Each stub in tests/Fixture/Worker is one worker
 * behaviour, and the pool is pointed at the one a test needs, so the scenario is the file rather
 * than a flag inside it.
 *
 * The one path deliberately left out is assertPoolAlive(): the "Worker died while analysing"
 * check in pump() fires first in every reachable scenario, see the note on that method.
 */
#[CoversClass(WorkerPool::class)]
final class WorkerPoolTest extends TestCase
{
    /** @var array<int, array{class: string, data: array<array-key, mixed>, stopOn: bool}> */
    private array $collected = [];

    protected function setUp(): void
    {
        $this->collected = [];
    }

    public function testEveryClassIsHandedOutOnceAndComesBackOnce(): void
    {
        $this->pool('answers', 3)->run(
            ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
            $this->record(...),
        );

        $classnames = array_column($this->collected, 'class');
        sort($classnames);

        self::assertSame(['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], $classnames);
    }

    public function testTheResultPayloadAndStopOnFlagTravelBack(): void
    {
        $this->pool('answers-stop-on', 1)->run(['C1'], $this->record(...));

        self::assertSame(
            [['class' => 'C1', 'data' => ['countPass' => 1], 'stopOn' => true]],
            $this->collected,
        );
    }

    /**
     * A pipe hands over bytes, so a message can arrive in two pieces. Without the reader's buffer
     * the first piece would be decoded as invalid JSON and the run would abort.
     */
    public function testALineArrivingInTwoPiecesIsReassembled(): void
    {
        $this->pool('splits-lines', 1)->run(['C1'], $this->record(...));

        self::assertSame(
            [['class' => 'C1', 'data' => ['countPass' => 1], 'stopOn' => false]],
            $this->collected,
        );
    }

    /**
     * Regression: this is the message the worker autoload bug surfaced as, and it came out of a
     * branch no test had ever run. The worker's STDERR is what makes it diagnosable at all.
     */
    public function testAWorkerDyingOnAJobIsReportedWithItsExitCodeAndStderr(): void
    {
        try {
            $this->pool('dies', 1)->run(['C1'], $this->record(...));

            self::fail('a worker dying on a job must abort the run');
        } catch (WorkerProtocolException $workerProtocolException) {
            self::assertStringContainsString(
                'Worker died while analysing "C1"',
                $workerProtocolException->getMessage(),
            );
            self::assertStringContainsString('exit code 3', $workerProtocolException->getMessage());
            self::assertStringContainsString('stub could not boot', $workerProtocolException->getMessage());
        }
    }

    /**
     * What the reader refuses must reach the caller: the pool may not swallow it, nor turn it into
     * a missing result. Every refusal the reader can produce is covered in WorkerMessageReaderTest.
     */
    public function testWhatTheReaderRefusesAbortsTheRun(): void
    {
        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Unreadable worker output: not json');

        $this->pool('writes-garbage', 1)->run(['C1'], $this->record(...));
    }

    /**
     * Draining: the caller refuses the first result, so nothing more may be handed out. With a
     * single worker nothing else was in flight, so the run stops on exactly one result.
     */
    public function testRefusingAResultStopsFeedingTheQueue(): void
    {
        $refuse = function (string $classname, array $data, bool $stopOn): bool {
            $this->record($classname, $data, $stopOn);

            return false;
        };

        $this->pool('answers', 1)->run(['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], $refuse);

        self::assertCount(1, $this->collected);
    }

    /**
     * The early return has to happen before start(): the command line handed in here cannot even
     * be built, so reaching start() would throw instead of returning quietly.
     */
    public function testAnEmptyQueueStartsNothingAtAll(): void
    {
        $finder = self::createStub(PhpExecutableFinder::class);
        $finder->method('find')->willReturn(false);

        $pool = new WorkerPool(4, [], new ComposerAutoloadLocator(), new WorkerCommandLine($finder));

        $pool->run([], $this->record(...));

        self::assertSame([], $this->collected);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function record(string $classname, array $data, bool $stopOn): bool
    {
        $this->collected[] = ['class' => $classname, 'data' => $data, 'stopOn' => $stopOn];

        return true;
    }

    /**
     * @param string $stub basename of a worker behaviour in tests/Fixture/Worker
     */
    private function pool(string $stub, int $size): WorkerPool
    {
        return new WorkerPool(
            $size,
            [],
            new ComposerAutoloadLocator(),
            new WorkerCommandLine(
                packageBinary: \dirname(__DIR__, 4) . '/tests/Fixture/Worker/' . $stub . '.php',
            ),
        );
    }
}
