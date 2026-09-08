<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use Phar;
use StructuraPhp\Structura\Console\Commands\WorkerCommand;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Builds the command line and the environment a worker process is started with.
 *
 * Kept out of WorkerPool because none of it needs a running process: the PHP binary, the entry
 * point to re-enter and the autoload hand-off are pure resolution, and resolving them here is
 * what makes their failure paths reachable from a test.
 */
final readonly class WorkerCommandLine
{
    /**
     * @param null|string $packageBinary entry point candidate, defaulting to this package's own
     *                                   bin/structura; overridden by tests to point the pool at a
     *                                   stub worker, or at a path that does not exist
     */
    public function __construct(
        private PhpExecutableFinder $phpFinder = new PhpExecutableFinder(),
        private ?string $packageBinary = null,
    ) {}

    /**
     * @param array<int, string> $workerOptions CLI options replayed on the worker
     *
     * @return array<int, string>
     *
     * @throws WorkerProtocolException when the PHP binary or the entry point cannot be resolved
     */
    public function build(array $workerOptions): array
    {
        $php = $this->phpFinder->find(false);
        if ($php === false) {
            throw new WorkerProtocolException('Unable to locate the PHP binary to start workers.');
        }

        return [
            $php,
            ...$this->phpFinder->findArguments(),
            $this->entryPoint(),
            WorkerCommand::NAME,
            ...$workerOptions,
        ];
    }

    /**
     * Environment handed to a worker so it boots on the same autoload file as this process.
     *
     * Takes the already resolved path rather than the locator: a worker has to require an
     * autoload file before any Structura class exists, so the question is answered in the parent
     * and only its answer travels. An option could not carry it, since reading an option means
     * booting the console first -- see the README, invariant 8.3.
     *
     * @param null|string $autoload path resolved by ComposerAutoloadLocator, null when there is
     *                              none to forward
     *
     * @return array<string, string> merged with the inherited environment by Process
     */
    public function env(?string $autoload): array
    {
        return $autoload !== null
            ? [ComposerAutoloadLocator::ENV_VARIABLE => $autoload]
            : [];
    }

    /**
     * File a worker re-enters Structura through.
     *
     * Resolved from the package itself rather than from $_SERVER['argv'][0], because the parent
     * is not necessarily the structura binary: the analysis can be driven from a PHPUnit test or
     * from any other host application.
     */
    private function entryPoint(): string
    {
        $phar = Phar::running(false);
        if ($phar !== '') {
            return $phar;
        }

        $binary = $this->packageBinary ?? \dirname(__DIR__, 3) . '/bin/structura';
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
