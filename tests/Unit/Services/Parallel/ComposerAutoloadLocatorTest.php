<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Services\Parallel\ComposerAutoloadLocator;

#[CoversClass(ComposerAutoloadLocator::class)]
final class ComposerAutoloadLocatorTest extends TestCase
{
    public function testLocatesTheAutoloadFileTheCurrentProcessBootedOn(): void
    {
        $autoload = (new ComposerAutoloadLocator())->locate();

        self::assertSame(\dirname(__DIR__, 4) . '/vendor/autoload.php', $autoload);
    }

    public function testTheEnvironmentVariableNameIsTheOneBinStructuraReads(): void
    {
        $bootstrap = file_get_contents(\dirname(__DIR__, 4) . '/bin/structura');

        self::assertIsString($bootstrap);
        self::assertStringContainsString(
            \sprintf("getenv('%s')", ComposerAutoloadLocator::ENV_VARIABLE),
            $bootstrap,
        );
    }
}
