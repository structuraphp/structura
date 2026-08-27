<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use Closure;
use Phar;
use StructuraPhp\Structura\Console\Commands\WorkerCommand;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\PhpExecutableFinder;
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
 */
final class WorkerPool
{
    /** @var int microseconds slept between two polls of the worker outputs */
    private const POLL_INTERVAL = 1000;

    /** @var array<int, Process> */
    private array $processes = [];

    /** @var array<int, InputStream> */
    private array $inputStreams = [];

    /** @var array<int, string> partial STDOUT line still waiting for its newline, per worker */
    private array $buffers = [];

    /** @var array<int, null|string> class currently being analysed by each worker */
    private array $inFlight = [];

    /**
     * @param array<int, string> $workerOptions CLI options forwarded to every worker
     */
    public function __construct(
        private readonly int $size,
        private readonly array $workerOptions = [],
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
        $command = $this->baseCommand();

        for ($index = 0; $index < $count; $index++) {
            $inputStream = new InputStream();
            $process = new Process([...$command, ...$this->workerOptions]);
            $process->setInput($inputStream);
            $process->setTimeout(null);
            $process->start();

            $this->processes[$index] = $process;
            $this->inputStreams[$index] = $inputStream;
            $this->buffers[$index] = '';
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
                foreach ($this->readLines($index, $process) as $line) {
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
     * @return array<int, string> complete lines emitted since the last read
     */
    private function readLines(int $index, Process $process): array
    {
        $this->buffers[$index] .= $process->getIncrementalOutput();

        $lines = explode("\n", $this->buffers[$index]);
        // The trailing element is either an empty string or a partial line: keep it buffered.
        $this->buffers[$index] = array_pop($lines);

        return array_values(array_filter($lines, static fn (string $line): bool => trim($line) !== ''));
    }

    /**
     * @param Closure(string, array<array-key, mixed>, bool): bool $onResult
     *
     * @return bool false when the caller asked to stop
     */
    private function handleLine(int $index, string $line, Closure $onResult): bool
    {
        $this->inFlight[$index] = null;

        /** @var mixed $message */
        $message = json_decode($line, true);
        if (!\is_array($message)) {
            throw new WorkerProtocolException('Unreadable worker output: ' . $line);
        }

        if (($message['type'] ?? null) === 'error') {
            throw new WorkerProtocolException(
                \sprintf(
                    'Worker failed on "%s": %s',
                    \is_string($message['class'] ?? null) ? $message['class'] : 'unknown',
                    \is_string($message['message'] ?? null) ? $message['message'] : 'unknown error',
                ),
            );
        }

        $classname = $message['class'] ?? null;
        $data = $message['data'] ?? null;
        if (!\is_string($classname) || !\is_array($data)) {
            throw new WorkerProtocolException('Incomplete worker result: ' . $line);
        }

        return $onResult($classname, $data, ($message['stopOn'] ?? false) === true);
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
        $this->buffers = [];
        $this->inFlight = [];
    }

    /**
     * Command re-entering the Structura entry point.
     *
     * Resolved from the package itself rather than from $_SERVER['argv'][0], because the parent
     * is not necessarily the structura binary: the analysis can be driven from a PHPUnit test or
     * from any other host application.
     *
     * @return array<int, string>
     */
    private function baseCommand(): array
    {
        $finder = new PhpExecutableFinder();
        $php = $finder->find(false);
        if ($php === false) {
            throw new WorkerProtocolException('Unable to locate the PHP binary to start workers.');
        }

        return [$php, ...$finder->findArguments(), $this->entryPoint(), WorkerCommand::NAME];
    }

    private function entryPoint(): string
    {
        $phar = Phar::running(false);
        if ($phar !== '') {
            return $phar;
        }

        $binary = \dirname(__DIR__, 3) . '/bin/structura';
        if (is_file($binary)) {
            return $binary;
        }

        $argv = $_SERVER['argv'] ?? null;

        $entryPoint = \is_array($argv) ? ($argv[0] ?? null) : null;

        return \is_string($entryPoint) && $entryPoint !== ''
            ? $entryPoint
            : throw new WorkerProtocolException(
                'Unable to determine the Structura entry point to start workers.',
            );
    }
}
