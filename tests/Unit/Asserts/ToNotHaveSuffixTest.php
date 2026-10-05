<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveSuffix;
use StructuraPhp\Structura\Concerns\Expr\NameAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveSuffix::class)]
#[CoversMethod(NameAssert::class, 'toNotHaveSuffix')]
final class ToNotHaveSuffixTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutSuffixProvider')]
    public function testToNotHaveSuffix(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveSuffix('Interface'),
            );

        self::assertRulesPass(
            $rules,
            'to not have suffix <promote>Interface</promote>',
        );
    }

    public static function getClassLikeWithoutSuffixProvider(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with suffix inside the name' => ['<?php class InterfaceFoo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithSuffixProvider')]
    public function testShouldFailToNotHaveSuffix(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveSuffix('Interface'),
            );

        self::assertRulesViolation(
            $rules,
            'Resource name <promote>FooInterface</promote> must not end with <fire>Interface</fire>',
        );
    }

    public static function getClassLikeWithSuffixProvider(): Generator
    {
        yield 'class' => ['<?php class FooInterface {}'];

        yield 'enum' => ['<?php enum FooInterface {}'];

        yield 'interface' => ['<?php interface FooInterface {}'];

        yield 'trait' => ['<?php trait FooInterface {}'];
    }
}
