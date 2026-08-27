<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Console\Commands\WorkerCommand;
use StructuraPhp\Structura\Console\Enums\CommonOption;
use StructuraPhp\Structura\Console\Kernel;
use StructuraPhp\Structura\Tests\Feature\TestConfig;
use StructuraPhp\Structura\Tests\Feature\TestVoid;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Exercises the worker as the parent actually drives it: a real child process speaking NDJSON
 * over its standard streams. Anything on STDOUT that is not a protocol line would desynchronise
 * the pool, so that is what these tests pin down.
 */
#[CoversClass(WorkerCommand::class)]
final class WorkerCommandTest extends TestCase
{
    public function testAnswersOneNdjsonLinePerJob(): void
    {
        $lines = $this->runWorker($this->job(TestVoid::class));

        self::assertCount(1, $lines);

        $message = json_decode($lines[0], true);
        self::assertIsArray($message);
        self::assertSame('result', $message['type']);
        self::assertSame(TestVoid::class, $message['class']);
        self::assertFalse($message['stopOn']);
        self::assertIsArray($message['data']);
        self::assertArrayHasKey('tests', $message['data']);
    }

    public function testHandlesSeveralJobsInOrderOnTheSameProcess(): void
    {
        $lines = $this->runWorker($this->job(TestVoid::class)
        . $this->job(TestConfig::class));

        self::assertCount(2, $lines);
        self::assertSame(
            [
                TestVoid::class,
                TestConfig::class,
            ],
            array_map(
                static function (string $line): string {
                    /** @var array<string, string> $message */
                    $message = json_decode($line, true);

                    return $message['class'];
                },
                $lines,
            ),
        );
    }

    public function testUnknownClassIsReportedAsAnErrorMessage(): void
    {
        $lines = $this->runWorker($this->job('Acme\Nope'));

        self::assertCount(1, $lines);

        $message = json_decode($lines[0], true);
        self::assertIsArray($message);
        self::assertSame('error', $message['type']);
        self::assertIsString($message['message']);
        self::assertStringContainsString('Unknown test class', $message['message']);
    }

    public function testBlankLinesAreIgnoredAndEofExitsCleanly(): void
    {
        $process = $this->process();
        $process->setInput("\n\n");
        $process->run();

        self::assertSame(0, $process->getExitCode());
        self::assertSame('', trim($process->getOutput()));
    }

    public function testMalformedJobIsReportedWithoutKillingTheWorker(): void
    {
        $lines = $this->runWorker("not json\n" . $this->job(TestVoid::class));

        self::assertCount(2, $lines);

        $first = json_decode($lines[0], true);
        self::assertIsArray($first);
        self::assertSame('error', $first['type']);
        self::assertIsString($first['message']);
        self::assertStringContainsString('Malformed job', $first['message']);

        $second = json_decode($lines[1], true);
        self::assertIsArray($second);
        self::assertSame('result', $second['type']);
    }

    /**
     * Regression: the autoload step used to warn on STDOUT, which desynchronised the parent's
     * NDJSON line parser as soon as the analysis ran from a PHAR.
     */
    public function testStdoutCarriesNothingButProtocolLines(): void
    {
        $process = $this->process();
        $process->setInput($this->job(TestVoid::class));
        $process->run();

        foreach (explode("\n", trim($process->getOutput())) as $line) {
            if (trim($line) === '') {
                continue;
            }

            self::assertIsArray(
                json_decode($line, true),
                'every STDOUT line must be a JSON protocol message, got: ' . $line,
            );
        }
    }

    public function testCommandIsHiddenFromTheCommandList(): void
    {
        $command = (new Kernel())->find(WorkerCommand::NAME);

        self::assertTrue($command->isHidden());
    }

    private function job(string $classname): string
    {
        return json_encode(['class' => $classname], JSON_UNESCAPED_SLASHES) . "\n";
    }

    /**
     * @return array<int, string>
     */
    private function runWorker(string $input): array
    {
        $process = $this->process();
        $process->setInput($input);
        $process->run();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());

        return array_values(array_filter(
            explode("\n", trim($process->getOutput())),
            static fn (string $line): bool => trim($line) !== '',
        ));
    }

    private function process(): Process
    {
        $php = (new PhpExecutableFinder())->find(false);
        self::assertIsString($php);

        $process = new Process([
            $php,
            \dirname(__DIR__, 4) . '/bin/structura',
            WorkerCommand::NAME,
            '--' . CommonOption::Config->value . '=' . \dirname(__DIR__, 4) . '/structura.php',
        ]);
        $process->setTimeout(60);

        return $process;
    }
}
