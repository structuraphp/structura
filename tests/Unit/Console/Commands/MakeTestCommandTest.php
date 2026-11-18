<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Console\Commands\MakeTestCommand;
use StructuraPhp\Structura\Console\Kernel;
use StructuraPhp\Structura\Tests\Helper\Sandbox;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(MakeTestCommand::class)]
final class MakeTestCommandTest extends TestCase
{
    use Sandbox;

    private string $config;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->enterSandbox();
        mkdir('src');

        $this->config = \sprintf(
            '%s/Fixture/Commands/MakeTestCommand/structura.php',
            \dirname(__DIR__, 3),
        );

        $application = new Kernel();

        $command = $application->find(MakeTestCommand::NAME);
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->leaveSandbox();
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
        ];

        $outputs = array_slice(
            array: explode(PHP_EOL, $display),
            offset: 4,
            length: 5,
        );

        self::assertCount(\count($exceptOutput), $outputs);
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
