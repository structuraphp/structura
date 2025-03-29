<?php

declare(strict_types=1);

use Structura\Builder\SutBuilder;
use Structura\Configs\StructuraConfig;
use Structura\Tests\Unit\Asserts\ToUseStrictTypesTest;

return static function (StructuraConfig $config): void {
    $config->archiRootNamespace(
        'Structura\Tests\Feature',
        'tests/Feature',
    );

    $config->sut(
        static fn(SutBuilder $sutBuilder) => $sutBuilder
            ->appRootNamespace('Structura', 'src')
            ->testRootNamespace('Structura\Tests\Unit', 'tests/Unit')
            ->expect(ToUseStrictTypesTest::class),
    );
};
