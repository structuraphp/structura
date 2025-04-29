<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Structura\Configs\StructuraConfig;
use Structura\Services\MakeTestService;
use Structura\ValueObjects\MakeTestValueObject;

#[CoversClass(MakeTestService::class)]
final class MakeTestServiceTest extends TestCase
{
    public function testAnalyseService(): void
    {
        $service = new MakeTestService(
            StructuraConfig::make()
                ->archiRootNamespace(
                    'Structura\Tests\Feature',
                    'tests/Feature',
                ),
        );

        $makeTestValueObject = $service->make(
            new MakeTestValueObject(
                testClassName: 'Void',
                path: 'src',
            ),
        );

        $filename = dirname(__DIR__, 2) . '/Feature/TestVoid.php';

        self::assertSame('TestVoid', $makeTestValueObject->className);
        self::assertEquals($filename, $makeTestValueObject->filename);
        self::assertEquals(file_get_contents($filename), $makeTestValueObject->content);
    }
}
