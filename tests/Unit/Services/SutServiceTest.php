<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Structura\Builder\SutBuilder;
use Structura\Services\SutService;
use Structura\Tests\Unit\Asserts\AndTest;
use Structura\Tests\Unit\Asserts\OrTest;
use Structura\Tests\Unit\Asserts\ToBeInterfaceTest;
use Structura\Tests\Unit\Asserts\ToBeInvokableTest;
use Structura\Tests\Unit\Asserts\ToUseStrictTypesTest;
use Structura\ValueObjects\RootNamespaceValueObject;
use Structura\ValueObjects\RuleSutValuesObject;

class SutServiceTest extends TestCase
{
    public function testAnalyseService(): void
    {
        $rulesDto = new RuleSutValuesObject(
            appRootNamespace: new RootNamespaceValueObject(
                'Structura',
                'src',
            ),
            testRootNamespace: new RootNamespaceValueObject(
                'Structura\Tests\Unit',
                'tests/Unit',
            ),
            expects: [AndTest::class],
        );

        $service = new SutService($rulesDto);

        $sutValueObject = $service->analyse();
        self::assertSame(25, $sutValueObject->countPass);
        self::assertSame(4, $sutValueObject->countViolation);
        self::assertSame(
            [
                'Structura\Asserts\Or' => OrTest::class,
                'Structura\Asserts\ToBeInterface' => ToBeInterfaceTest::class,
                'Structura\Asserts\ToBeInvokable' => ToBeInvokableTest::class,
                'Structura\Asserts\ToUseStrictTypes' => ToUseStrictTypesTest::class,
            ],
            $sutValueObject->violations,
        );
    }
}
