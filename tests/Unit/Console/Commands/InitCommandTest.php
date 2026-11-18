<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Console\Commands\InitCommand;
use StructuraPhp\Structura\Console\Kernel;
use StructuraPhp\Structura\Tests\Helper\Sandbox;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(InitCommand::class)]
final class InitCommandTest extends TestCase
{
    use Sandbox;

    private const CONFIG_PATH = 'structura.php';

    protected function setUp(): void
    {
        $this->enterSandbox();
    }

    protected function tearDown(): void
    {
        $this->leaveSandbox();
    }

    public function testShouldInitConfig(): void
    {
        $application = new Kernel();

        $command = $application->find(InitCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);

        $commandTester->execute(['--config' => self::CONFIG_PATH]);

        $display = $commandTester->getDisplay();

        self::assertStringContainsString('Version:', $display);
        self::assertStringContainsString(
            \sprintf(
                'No "%s" config found. Should we generate it for you? [yes]:',
                self::CONFIG_PATH,
            ),
            $display,
        );
        self::assertStringContainsString(
            'The config is added now.',
            $display,
        );

        $commandTester->assertCommandIsSuccessful();

        $commandTester->execute(['--config' => self::CONFIG_PATH]);

        self::assertSame(Command::INVALID, $commandTester->getStatusCode());
        self::assertStringContainsString('already exists', $commandTester->getDisplay());
    }
}
