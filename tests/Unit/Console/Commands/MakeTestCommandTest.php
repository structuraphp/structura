<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Console\Commands\MakeTestCommand;
use StructuraPhp\Structura\Console\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(MakeTestCommand::class)]
final class MakeTestCommandTest extends TestCase
{
    private string $filename;

    private string $config;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->filename = \dirname(__DIR__, 3) . '/Architecture/TestModel.php';
        $this->config = \sprintf(
            '%s/Fixture/Commands/MakeTestCommand/structura.php',
            \dirname(__DIR__, 3),
        );

        $application = new Kernel();

        $command = $application->find(MakeTestCommand::getDefaultName() ?? '');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
            rmdir(\dirname(__DIR__, 3) . '/Architecture');
        }
    }

    public function testShouldMake(): void
    {
        $statusCode = $this
            ->commandTester
            ->setInputs(['Model', 'src'])
            ->execute([
                '--config' => $this->config,
            ]);

        $display = rtrim($this->commandTester->getDisplay());

        $exceptOutput = [
            ' What is the name of the test class (e.g. "NamespaceName\ClassName")?:',
            ' >',
            ' Source code path that your test will analyze [src]:',
            ' >',
            ' [INFO] Test file is added now, run composer dump-autoload.',
            sprintf(
                '        file://%s/Architecture/TestModel.php',
                dirname(__DIR__, 3),
            ),
        ];

        $outputs = array_slice(explode(PHP_EOL, $display), 4);

        foreach ($outputs as $key => $command) {
            self::assertSame($exceptOutput[$key], rtrim($command));
        }

        self::assertSame(Command::SUCCESS, $statusCode);
    }

    public function testMakeTestFail(): void
    {
        $statusCode = $this->commandTester
            ->setInputs(['', ''])
            ->execute([
                '--config' => $this->config,
            ]);

        $display = $this->commandTester->getDisplay();

        self::assertStringContainsString(
            '[ERROR] The name of the test class is required',
            $display,
        );
        self::assertSame(Command::INVALID, $statusCode);
    }

    public function testMakeTestFailWithFileAlreadyExist(): void
    {
        $this->commandTester
            ->setInputs(['Model', 'src'])
            ->execute([
                '--config' => $this->config,
            ]);

        $statusCode = $this->commandTester
            ->setInputs(['Model', 'src'])
            ->execute([
                '--config' => $this->config,
            ]);
        $display = $this->commandTester->getDisplay();

        self::assertStringContainsString('already exists', $display);
        self::assertSame(Command::INVALID, $statusCode);
    }
}
