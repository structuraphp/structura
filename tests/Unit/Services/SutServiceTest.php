<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Services\SutService;
use StructuraPhp\Structura\Tests\Unit\Asserts\AndTest;
use StructuraPhp\Structura\Tests\Unit\Asserts\OrTest;
use StructuraPhp\Structura\Tests\Unit\Asserts\ToBeInvokableTest;
use StructuraPhp\Structura\Tests\Unit\Asserts\ToUseStrictTypesTest;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;
use StructuraPhp\Structura\ValueObjects\RuleSutValuesObject;

#[CoversClass(SutService::class)]
class SutServiceTest extends TestCase
{
    public function testAnalyseService(): void
    {
        $rulesDto = new RuleSutValuesObject(
            appRootNamespace: new RootNamespaceValueObject(
                'StructuraPhp\Structura',
                'src',
            ),
            testRootNamespace: new RootNamespaceValueObject(
                'StructuraPhp\Structura\Tests\Unit',
                'tests/Unit',
            ),
            expects: [AndTest::class],
        );

        $service = new SutService($rulesDto);

        $sutValueObject = $service->analyse();
        self::assertSame(39, $sutValueObject->countPass);
        self::assertSame(3, $sutValueObject->countViolation);
        self::assertSame(
            [
                'StructuraPhp\Structura\Asserts\Or' => OrTest::class,
                'StructuraPhp\Structura\Asserts\ToBeInvokable' => ToBeInvokableTest::class,
                'StructuraPhp\Structura\Asserts\ToUseStrictTypes' => ToUseStrictTypesTest::class,
            ],
            $sutValueObject->violations,
        );
    }
}
