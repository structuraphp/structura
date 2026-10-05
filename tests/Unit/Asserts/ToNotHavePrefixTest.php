<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHavePrefix;
use StructuraPhp\Structura\Concerns\Expr\NameAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHavePrefix::class)]
#[CoversMethod(NameAssert::class, 'toNotHavePrefix')]
final class ToNotHavePrefixTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutPrefixProvider')]
    public function testToNotHavePrefix(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHavePrefix('Abstract'),
            );

        self::assertRulesPass(
            $rules,
            'to not have prefix <promote>Abstract</promote>',
        );
    }

    public static function getClassLikeWithoutPrefixProvider(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with prefix inside the name' => ['<?php class FooAbstract {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithPrefixProvider')]
    public function testShouldFailToNotHavePrefix(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHavePrefix('Abstract'),
            );

        self::assertRulesViolation(
            $rules,
            'Resource name <promote>AbstractFoo</promote> must not start with <fire>Abstract</fire>',
        );
    }

    public static function getClassLikeWithPrefixProvider(): Generator
    {
        yield 'class' => ['<?php abstract class AbstractFoo {}'];

        yield 'enum' => ['<?php enum AbstractFoo {}'];

        yield 'interface' => ['<?php interface AbstractFoo {}'];

        yield 'trait' => ['<?php trait AbstractFoo {}'];
    }
}
