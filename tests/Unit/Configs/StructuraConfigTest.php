<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Configs;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;

#[CoversClass(StructuraConfig::class)]
final class StructuraConfigTest extends TestCase
{
    public function testBaselineIsNullByDefault(): void
    {
        self::assertNull(StructuraConfig::make()->getConfig()->baseline);
    }

    public function testBaselineIsStored(): void
    {
        $config = StructuraConfig::make()->baseline('structura-baseline.php')->getConfig();

        self::assertSame('structura-baseline.php', $config->baseline);
    }
}
