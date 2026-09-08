<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Console\Commands\WorkerCommand;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\Services\Parallel\ComposerAutoloadLocator;
use StructuraPhp\Structura\Services\Parallel\WorkerCommandLine;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Covers how a worker is addressed before it exists.
 *
 * Every failure here surfaces to the user as "Worker died while analysing ..." with an exit code
 * and no explanation, so the resolution order and the two refusals are worth pinning down. The
 * entry point candidate is injectable, which is what makes the fallbacks reachable at all: they
 * are computed from this package's own location and would otherwise always resolve.
 */
#[CoversClass(WorkerCommandLine::class)]
final class WorkerCommandLineTest extends TestCase
{
    /** @var mixed value of['argv'] before a test touched it */
    private mixed $argv = null;

    private bool $hadArgv = false;

    protected function setUp(): void
    {
        $this->hadArgv = \array_key_exists('argv', $_SERVER);
        $this->argv = $_SERVER['argv'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->hadArgv) {
            $_SERVER['argv'] = $this->argv;

            return;
        }

        unset($_SERVER['argv']);
    }

    public function testWorkersAreStartedOnThePackageBinary(): void
    {
        $php = (new PhpExecutableFinder())->find(false);
        self::assertIsString($php);

        $command = (new WorkerCommandLine())->build(['--config=/tmp/structura.php']);

        self::assertSame($php, $command[0]);
        self::assertSame(
            [
                \dirname(__DIR__, 4) . '/bin/structura',
                WorkerCommand::NAME,
                '--config=/tmp/structura.php',
            ],
            \array_slice($command, -3),
        );
    }

    public function testWorkerOptionsAreAppendedInOrder(): void
    {
        $command = (new WorkerCommandLine())->build(['--first', '--second=2']);

        self::assertSame(['--first', '--second=2'], \array_slice($command, -2));
    }

    public function testAMissingPhpBinaryIsReported(): void
    {
        $finder = self::createStub(PhpExecutableFinder::class);
        $finder->method('find')->willReturn(false);

        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Unable to locate the PHP binary to start workers.');

        (new WorkerCommandLine($finder))->build([]);
    }

    /**
     * Last resort when the package binary is gone: the command that started the parent. It is a
     * poor candidate -- the parent may be phpunit rather than structura -- but it beats refusing.
     */
    public function testTheCallingCommandIsUsedWhenThePackageBinaryIsMissing(): void
    {
        $_SERVER['argv'] = ['/usr/local/bin/host-application', 'analyze'];

        $command = (new WorkerCommandLine(packageBinary: '/does/not/exist/structura'))->build([]);

        self::assertSame(
            ['/usr/local/bin/host-application', WorkerCommand::NAME],
            \array_slice($command, -2),
        );
    }

    /**
     * An empty argv, a blank program name, or an argv that is not even an array: nothing usable
     * is left, and refusing with the right message beats starting a process that cannot work.
     */
    #[TestWith([[]])]
    #[TestWith([['']])]
    #[TestWith(['not an array at all'])]
    public function testNoCandidateAtAllIsReported(mixed $argv): void
    {
        $_SERVER['argv'] = $argv;

        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Unable to determine the Structura entry point to start workers.');

        (new WorkerCommandLine(packageBinary: '/does/not/exist/structura'))->build([]);
    }

    public function testAnAbsentArgvIsReportedToo(): void
    {
        unset($_SERVER['argv']);

        $this->expectException(WorkerProtocolException::class);
        $this->expectExceptionMessage('Unable to determine the Structura entry point to start workers.');

        (new WorkerCommandLine(packageBinary: '/does/not/exist/structura'))->build([]);
    }

    public function testTheAutoloadPathTravelsInTheEnvironment(): void
    {
        self::assertSame(
            [ComposerAutoloadLocator::ENV_VARIABLE => '/app/vendor/autoload.php'],
            (new WorkerCommandLine())->env('/app/vendor/autoload.php'),
        );
    }

    /**
     * Nothing to forward must leave the inherited environment untouched rather than export an
     * empty variable, which bin/structura would have to special case.
     */
    public function testNothingIsExportedWhenThereIsNoAutoloadToForward(): void
    {
        self::assertSame([], (new WorkerCommandLine())->env(null));
    }
}
