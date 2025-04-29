<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Structura\Console\Commands\MakeTestCommand;
use Structura\Console\Kernel;
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

        $display = $this->commandTester->getDisplay();

        self::assertStringContainsString(
            '[INFO] Test file TestModel is added now',
            $display,
        );
        self::assertSame(Command::SUCCESS, $statusCode);
    }

    public function testMakeTestFail(): void
    {
        $statusCode = $this->commandTester
            ->setInputs([''])
            ->execute([
                '--config' => $this->config,
            ]);

        $display = $this->commandTester->getDisplay();

        self::assertStringContainsString(
            '[ERROR] Test name is required',
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

        self::assertStringContainsString(
            sprintf('[ERROR] File %s already exists', $this->filename),
            str_replace([' ', PHP_EOL], [' ', ' '], $display),
        );
        self::assertSame(Command::FAILURE, $statusCode);
    }
}
