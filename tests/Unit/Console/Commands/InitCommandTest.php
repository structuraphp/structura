<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\TestCase;
use Structura\Console\Commands\InitCommand;
use Structura\Console\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends TestCase
{
    private const CONFIG_PATH = __DIR__ . '/structura.php';

    protected function tearDown(): void
    {
        if (file_exists(self::CONFIG_PATH)) {
            unlink(self::CONFIG_PATH);
        }
    }

    public function testShouldInitConfig(): void
    {
        $application = new Kernel();

        $command = $application->find(InitCommand::getDefaultName() ?? '');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);

        $commandTester->execute(['--config' => self::CONFIG_PATH]);

        $display = $commandTester->getDisplay();

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
    }
}
