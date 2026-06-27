<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Services\AnalyseService;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Tests\Feature\TestAssert;
use StructuraPhp\Structura\Tests\Feature\TestConfig;

#[CoversClass(AnalyseService::class)]
final class AnalyseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $config = StructuraConfig::make()
            ->addTestSuite('tests/Feature', 'main')
            ->getConfig();

        (new FinderService($config))->getClassTests();
    }

    public function testAnalyseSingleClass(): void
    {
        $service = new AnalyseService();
        $result = $service->analyse(microtime(true), TestConfig::class);

        self::assertSame(0, $result->countViolation);
        self::assertSame(1, $result->countPass);
        self::assertSame(0, $result->countWarning);
        self::assertSame(0, $result->countNotice);
    }

    public function testAnalyseSingleClassWithFilter(): void
    {
        $service = new AnalyseService(filter: 'nonexistent');
        $result = $service->analyse(microtime(true), TestConfig::class);

        self::assertSame(0, $result->countViolation);
        self::assertSame(0, $result->countPass);
    }

    public function testStopOnError(): void
    {
        $service = new AnalyseService(stopOnError: true);

        $this->expectException(StopOnException::class);
        $service->analyse(microtime(true), TestAssert::class);
    }
}
