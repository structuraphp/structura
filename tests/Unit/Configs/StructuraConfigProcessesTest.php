<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Configs;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;

#[CoversClass(StructuraConfig::class)]
final class StructuraConfigProcessesTest extends TestCase
{
    /**
     * Parallelism stays opt-in: an untouched configuration must keep the analysis sequential.
     */
    public function testAnalysisIsSequentialByDefault(): void
    {
        self::assertSame(1, StructuraConfig::make()->getConfig()->processes);
    }

    #[TestWith([1])]
    #[TestWith([4])]
    #[TestWith([32])]
    public function testProcessesCanBeConfigured(int $processes): void
    {
        self::assertSame(
            $processes,
            StructuraConfig::make()->setProcesses($processes)->getConfig()->processes,
        );
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    public function testProcessesBelowOneIsRejected(int $processes): void
    {
        $this->expectException(InvalidArgumentException::class);

        StructuraConfig::make()->setProcesses($processes);
    }

    /**
     * "auto" is resolved at configuration time so nothing downstream ever sees a magic value.
     */
    public function testAutoResolvesToAConcreteCount(): void
    {
        self::assertGreaterThanOrEqual(
            1,
            StructuraConfig::make()->setProcessesAuto()->getConfig()->processes,
        );
    }
}
