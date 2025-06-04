<?php

declare(strict_types=1);

use StructuraPhp\Structura\Builder\SutBuilder;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Tests\Unit\Asserts\ToUseStrictTypesTest;

return static function (StructuraConfig $config): void {
    $config->archiRootNamespace(
        'StructuraPhp\Structura\Tests\Feature',
        'tests/Feature',
    );

    $config->sut(
        static fn (SutBuilder $sutBuilder) => $sutBuilder
            ->appRootNamespace('StructuraPhp\Structura\Services', 'src/Services')
            ->testRootNamespace('StructuraPhp\Structura\Tests\Unit\Services', 'tests/Unit/Services')
            ->expect(ToUseStrictTypesTest::class),
    );

    /*$config->sut(
        static fn (SutBuilder $sutBuilder) => $sutBuilder
            ->appRootNamespace('StructuraPhp\Structura\Asserts', 'src/Asserts')
            ->testRootNamespace('StructuraPhp\Structura\Tests\Unit\Asserts', 'tests/Unit/Asserts')
            ->expect(ToUseStrictTypesTest::class),
    );*/
};
