<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Console\Commands;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Console\Commands\AnalyseCommand;
use StructuraPhp\Structura\Console\Kernel;
use StructuraPhp\Structura\Services\BaselineService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Runs the real `tests/Feature` suite. Its rules analyse paths relative to the repository root:
 * the working directory stays there, only the configuration and the baseline go to a unique temp directory.
 * A full analysis is the costly part, so the baseline is generated once for the class, and the tests that
 * do not need the whole suite only run `TestController`.
 */
#[CoversClass(AnalyseCommand::class)]
final class AnalyseCommandTest extends TestCase
{
    private const STALE = 'no longer matches any violation';

    private const BASELINE = 'structura-baseline.php';

    /** Small part of the suite that still produces violations */
    private const FAST = ['--filter' => 'TestController'];

    private static string $template = '';

    private static int $generationStatus = -1;

    private static string $generationDisplay = '';

    private string $directory;

    public static function setUpBeforeClass(): void
    {
        $directory = self::makeDirectory();

        // no baseline declared: the file goes beside the configuration
        $tester = self::execute($directory, ['--generate-baseline' => true], null);
        self::$generationStatus = $tester->getStatusCode();
        self::$generationDisplay = $tester->getDisplay();

        $file = $directory . '/' . self::BASELINE;
        self::$template = is_file($file) ? (string) file_get_contents($file) : '';

        (new Filesystem())->remove($directory);
    }

    protected function setUp(): void
    {
        $this->directory = self::makeDirectory();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->directory);
    }

    public function testGenerateBaselineDefaultsBesideTheConfiguration(): void
    {
        self::assertSame(Command::SUCCESS, self::$generationStatus);
        self::assertStringContainsString('Baseline generated in', self::$generationDisplay);

        $this->writeBaseline(self::$template);
        self::assertNotSame(
            [],
            BaselineService::fromFile($this->directory . '/' . self::BASELINE, $this->directory)->getStaleWarnings(),
        );
    }

    public function testGenerateBaselineWritesTheDeclaredFile(): void
    {
        $tester = self::execute($this->directory, ['--generate-baseline' => true], 'custom-baseline.php');

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertFileExists($this->directory . '/custom-baseline.php');
        self::assertFileDoesNotExist($this->directory . '/' . self::BASELINE);
    }

    public function testAnalyseWithBaselineSucceeds(): void
    {
        $this->writeBaseline(self::$template);

        $tester = $this->analyze();
        $json = $this->json($tester);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertArrayNotHasKey('errors', $json);
        self::assertGreaterThan(0, $json['assertion_detail']['tolerated'] ?? 0);
        self::assertStringNotContainsString(self::STALE, $tester->getDisplay());
    }

    public function testRemovedEntryIsReportedAgain(): void
    {
        $entry = "                'StructuraPhp\\\\Structura\\\\Tests\\\\Fixture\\\\Contract\\\\ShouldQueueInterface',\n";
        self::assertStringContainsString($entry, self::$template);
        $this->writeBaseline(str_replace($entry, '', self::$template));

        $tester = $this->analyze();

        /** @var array<int, array{message: string}> $errors */
        $errors = $this->json($tester)['errors'] ?? [];

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertCount(1, $errors);
        self::assertStringContainsString('ShouldQueueInterface', $errors[0]['message']);
    }

    public function testUnknownEntryIsAStaleWarning(): void
    {
        $this->writeBaselineWithUnknownEntry();

        $tester = $this->analyze();

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString(self::STALE, $tester->getDisplay());
    }

    /**
     * @param array<string, bool|string> $partial
     */
    #[DataProvider('getPartialRunProvider')]
    public function testGenerateBaselineRefusesPartialRun(array $partial): void
    {
        $tester = $this->analyze(['--generate-baseline' => true] + $partial);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('--generate-baseline cannot be combined', $tester->getDisplay());
        self::assertFileDoesNotExist($this->directory . '/' . self::BASELINE);
    }

    /**
     * @param array<string, bool|string> $partial
     */
    #[DataProvider('getPartialRunProvider')]
    public function testPartialRunDoesNotReportStaleEntries(array $partial): void
    {
        $this->writeBaselineWithUnknownEntry();

        $tester = $this->analyze($partial);

        self::assertStringNotContainsString(self::STALE, $tester->getDisplay());
    }

    /**
     * @return iterable<string, array{array<string, bool|string>}>
     */
    public static function getPartialRunProvider(): iterable
    {
        yield 'filter' => [self::FAST];

        yield 'testsuite' => [['--testsuite' => 'main']];

        yield 'stop on error' => [['--stop-on-error' => true]];

        yield 'stop on warning' => [['--stop-on-warning' => true]];

        yield 'stop on notice' => [['--stop-on-notice' => true]];
    }

    public function testMissingBaselineFileFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            \sprintf('Baseline file "%s" not found', $this->directory . '/' . self::BASELINE),
        );

        $this->analyze();
    }

    public function testHeaderIsFollowedByABlankLine(): void
    {
        $display = $this->analyze(self::FAST, null)->getDisplay();

        self::assertStringContainsString('Configuration: ' . $this->directory . '/structura.php', $display);
        self::assertStringContainsString(PHP_EOL . PHP_EOL . '{"assertion_total"', $display);
    }

    public function testNoProgressHidesProgressAndPharWarning(): void
    {
        $display = $this->analyze(self::FAST, null)->getDisplay();

        self::assertStringNotContainsString('classe(s) from', $display);
        self::assertStringNotContainsString('PHAR', $display);
    }

    #[DataProvider('getErrorFormatProvider')]
    public function testErrorFormats(string $format, string $marker): void
    {
        $tester = $this->analyze(['--error-format' => $format] + self::FAST, null);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString($marker, $tester->getDisplay());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function getErrorFormatProvider(): iterable
    {
        yield 'text' => ['text', 'ERROR LIST'];

        yield 'github' => ['github', '::error file='];

        yield 'gitlab' => ['gitlab', '"fingerprint"'];

        yield 'pretty json' => ['pretty-json', '"assertion_total": '];
    }

    public function testProgressTextShowsEachTest(): void
    {
        $tester = self::execute($this->directory, ['--progress-format' => 'text', '--no-progress' => false] + self::FAST, null);

        self::assertStringContainsString('classe(s) from', $tester->getDisplay());
    }

    public function testProgressBarIsClosedWhenTheRunStops(): void
    {
        $tester = self::execute(
            $this->directory,
            ['--progress-format' => 'bar', '--no-progress' => false, '--stop-on-error' => true] + self::FAST,
            null,
        );

        self::assertStringContainsString(PHP_EOL . PHP_EOL . '{"assertion_total"', $tester->getDisplay());
    }

    public function testProgressBarRunsToTheEnd(): void
    {
        $tester = self::execute($this->directory, ['--progress-format' => 'bar', '--no-progress' => false] + self::FAST, null);

        self::assertStringContainsString('100%' . PHP_EOL . PHP_EOL, $tester->getDisplay());
    }

    /**
     * @param array<string, bool|string> $options
     */
    private function analyze(array $options = [], ?string $baseline = self::BASELINE): CommandTester
    {
        return self::execute($this->directory, $options, $baseline);
    }

    /**
     * @param array<string, bool|string> $options
     */
    private static function execute(string $directory, array $options, ?string $baseline): CommandTester
    {
        $config = $directory . '/structura.php';
        file_put_contents(
            $config,
            \sprintf(
                <<<'PHP'
                <?php

                declare(strict_types=1);

                use StructuraPhp\Structura\Configs\StructuraConfig;

                return static function (StructuraConfig $config): void {
                    $config->addTestSuite(%s, 'main');
                    %s
                };
                PHP,
                var_export(\dirname(__DIR__, 3) . '/Feature', true),
                $baseline === null ? '' : \sprintf('$config->baseline(%s);', var_export($baseline, true)),
            ),
        );

        $tester = new CommandTester((new Kernel())->find(AnalyseCommand::NAME));
        $tester->execute($options + ['--config' => $config, '--no-progress' => true, '--error-format' => 'json']);

        return $tester;
    }

    private static function makeDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/structura_cmd_' . bin2hex(random_bytes(8));
        mkdir($directory);

        return $directory;
    }

    /**
     * @return array{assertion_detail: array<string, int>, errors?: array<int, array<string, int|string>>}
     */
    private function json(CommandTester $tester): array
    {
        $lines = array_filter(
            explode(PHP_EOL, $tester->getDisplay()),
            static fn (string $line): bool => str_starts_with($line, '{'),
        );

        /** @var array{assertion_detail: array<string, int>, errors?: array<int, array<string, int|string>>} */
        return json_decode((string) end($lines), true);
    }

    private function writeBaseline(string $content): void
    {
        file_put_contents($this->directory . '/' . self::BASELINE, $content);
    }

    private function writeBaselineWithUnknownEntry(): void
    {
        self::assertStringContainsString("return [\n", self::$template);

        $this->writeBaseline(str_replace(
            "return [\n",
            "return [\n    'Tests\\\\Nope::testNope' => ['src/Nope.php' => ['ToNotDependOn' => ['Nope']]],\n",
            self::$template,
        ));
    }
}
