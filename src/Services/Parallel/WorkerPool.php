<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use Closure;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

/**
 * Pool of persistent worker processes fed by a shared job queue.
 *
 * Each worker is started once and then kept alive, receiving one job at a time on its STDIN and
 * answering with one NDJSON line on its STDOUT. Keeping workers alive amortises the PHP boot,
 * the configuration load and the require_once of the whole test suite, and hands out work
 * dynamically so a fast worker simply takes more of it.
 *
 * Uses symfony/process rather than pcntl so that parallel analysis works on every platform,
 * Windows included.
 *
 * Reading the protocol and building the worker command line live in WorkerMessageReader and
 * WorkerCommandLine: neither needs a running process, and keeping them out of here is what makes
 * their failure paths testable.
 */
final class WorkerPool
{
    /** @var int microseconds slept between two polls of the worker outputs */
    private const POLL_INTERVAL = 1000;

    /** @var array<int, Process> */
    private array $processes = [];

    /** @var array<int, InputStream> */
    private array $inputStreams = [];

    /** @var array<int, WorkerMessageReader> one protocol reader per worker */
    private array $readers = [];

    /** @var array<int, null|string> class currently being analysed by each worker */
    private array $inFlight = [];

    /**
     * @param array<int, string> $workerOptions CLI options forwarded to every worker
     */
    public function __construct(
        private readonly int $size,
        private readonly array $workerOptions = [],
        private readonly ComposerAutoloadLocator $autoloadLocator = new ComposerAutoloadLocator(),
        private readonly WorkerCommandLine $commandLine = new WorkerCommandLine(),
    ) {}

    /**
     * Runs every class through the pool, invoking $onResult as each one comes back.
     *
     * @param array<int, string> $classnames
     * @param Closure(string, array<array-key, mixed>, bool): bool $onResult receives the class name, the
     *                                                                       serialized result and whether the
     *                                                                       worker hit a stop-on threshold;
     *                                                                       returning false stops the run
     *
     * @throws WorkerProtocolException when a worker dies or speaks something unexpected
     */
    public function run(array $classnames, Closure $onResult): void
    {
        $queue = array_values($classnames);
        if ($queue === []) {
            return;
        }

        $this->start(min($this->size, \count($queue)));

        try {
            $this->pump($queue, $onResult);
        } finally {
            $this->close();
        }
    }

    private function start(int $count): void
    {
        $command = $this->commandLine->build($this->workerOptions);
        $env = $this->commandLine->env($this->autoloadLocator->locate());

        for ($index = 0; $index < $count; $index++) {
            $inputStream = new InputStream();
            $process = new Process($command, null, $env);
            $process->setInput($inputStream);
            $process->setTimeout(null);
            $process->start();

            $this->processes[$index] = $process;
            $this->inputStreams[$index] = $inputStream;
            $this->readers[$index] = new WorkerMessageReader();
            $this->inFlight[$index] = null;
        }
    }

    /**
     * @param array<int, string> $queue
     * @param Closure(string, array<array-key, mixed>, bool): bool $onResult
     */
    private function pump(array $queue, Closure $onResult): void
    {
        $pending = \count($queue);
        $draining = false;

        while ($pending > 0) {
            if (!$draining) {
                $this->dispatchJobs($queue);
            }

            $progressed = false;

            foreach ($this->processes as $index => $process) {
                foreach ($this->readers[$index]->push($process->getIncrementalOutput()) as $line) {
                    $progressed = true;
                    $pending--;

                    if (!$this->handleLine($index, $line, $onResult)) {
                        // Stop feeding the pool, but let the classes already dispatched finish so
                        // the caller still sees every result sequential mode would have produced.
                        $draining = true;
                    }
                }

                if ($this->inFlight[$index] !== null && !$process->isRunning()) {
                    throw new WorkerProtocolException(
                        \sprintf(
                            'Worker died while analysing "%s" (exit code %s): %s',
                            $this->inFlight[$index],
                            var_export($process->getExitCode(), true),
                            trim($process->getErrorOutput()),
                        ),
                    );
                }
            }

            if ($draining && !$this->hasInFlight()) {
                return;
            }

            if ($progressed) {
                continue;
            }

            $this->assertPoolAlive();

            usleep(self::POLL_INTERVAL);
        }
    }

    private function hasInFlight(): bool
    {
        foreach ($this->inFlight as $classname) {
            if ($classname !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Guards against every worker having exited while jobs remain, which would otherwise spin
     * the polling loop forever.
     *
     * Defensive only, and shadowed in practice: dispatchJobs() hands a job to every idle live
     * worker on each turn, so a dead worker almost always still holds an in flight class and the
     * "Worker died while analysing" check in pump() reports it first. Reaching this message would
     * take every worker dying in the window between answering and being fed again, which is why
     * no test covers it.
     */
    private function assertPoolAlive(): void
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                return;
            }
        }

        throw new WorkerProtocolException(
            'Every analysis worker exited before the test suite was fully analysed.',
        );
    }

    /**
     * Hands one job to every idle worker.
     *
     * @param array<int, string> $queue
     */
    private function dispatchJobs(array &$queue): void
    {
        foreach ($this->processes as $index => $process) {
            if ($this->inFlight[$index] !== null || $queue === [] || !$process->isRunning()) {
                continue;
            }

            $classname = array_shift($queue);
            $this->inFlight[$index] = $classname;
            $this->inputStreams[$index]->write(
                json_encode(['class' => $classname], JSON_UNESCAPED_SLASHES) . "\n",
            );
        }
    }

    /**
     * @param Closure(string, array<array-key, mixed>, bool): bool $onResult
     *
     * @return bool false when the caller asked to stop
     */
    private function handleLine(int $index, string $line, Closure $onResult): bool
    {
        $this->inFlight[$index] = null;

        $message = $this->readers[$index]->decode($line);

        return $onResult($message->classname, $message->data, $message->stopOn);
    }

    private function close(): void
    {
        foreach ($this->inputStreams as $inputStream) {
            $inputStream->close();
        }

        foreach ($this->processes as $process) {
            $process->stop(1.0);
        }

        $this->processes = [];
        $this->inputStreams = [];
        $this->readers = [];
        $this->inFlight = [];
    }
}
