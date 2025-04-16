<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Structura\Console\Commands\MakeTestCommand;
use Structura\Console\Kernel;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(MakeTestCommand::class)]
final class MakeTestCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        $filename = \dirname(__DIR__, 3) . '/Architecture/ModelTest.php';

        if (file_exists($filename)) {
            unlink($filename);
            rmdir(\dirname(__DIR__, 3) . '/Architecture');
        }
    }

    public function testShouldMakeTest(): void
    {
        $application = new Kernel();

        $command = $application->find(MakeTestCommand::getDefaultName() ?? '');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);

        $commandTester->execute([
            'name' => 'Model',
            '--config' => \sprintf(
                '%s/Fixture/Commands/MakeTestCommand/structura.php',
                \dirname(__DIR__, 3),
            ),
        ]);

        $display = $commandTester->getDisplay();

        self::assertStringContainsString(
            'Test file ModelTest is added now',
            $display,
        );

        $commandTester->assertCommandIsSuccessful();
    }
}
