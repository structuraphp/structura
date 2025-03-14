<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Testing\ArchitectureAsserts;

class ToHaveSuffixTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithSuffixProvider')]
    public function testToHaveSuffix(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toHaveSuffix('Controller'),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithoutSuffixProvider')]
    public function testShouldFailToHaveSuffix(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource name <promote>%s</promote> must end with <promote>Controller</promote>',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toHaveSuffix('Controller'),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithSuffixProvider(): Generator
    {
        yield 'class' => ['<?php class FooController {}'];
        yield 'enum' => ['<?php enum FooController {}'];
        yield 'interface' => ['<?php interface FooController {}'];
        yield 'trait' => ['<?php trait FooController {}'];
    }

    public static function getClassLikeWithoutSuffixProvider(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {}'];
        yield 'interface' => ['<?php interface Foo {}'];
        yield 'trait' => ['<?php trait Foo {}'];
    }
}
