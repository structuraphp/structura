<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveMethod;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveMethod::class)]
#[CoversMethod(MethodAssert::class, 'toNotHaveMethod')]
final class ToNotHaveMethodTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutMethod')]
    public function testToNotHaveMethod(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveMethod('bar'),
            );

        self::assertRulesPass(
            $rules,
            'to not have method <promote>bar</promote>',
        );
    }

    public static function getClassLikeWithoutMethod(): Generator
    {
        yield 'anonymous class' => ['<?php return new class { public function baz() {} };'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with another method' => ['<?php class Foo { public function baz() {} }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo { public function baz(); }'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithMethod')]
    public function testShouldFailToNotHaveMethod(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveMethod('bar'),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have method <fire>bar</fire>',
                $exceptName,
            ),
            3,
        );
    }

    public static function getClassLikeWithMethod(): Generator
    {
        $body = "{\n    public function baz() {}\n    public function bar() {}\n}";

        yield 'anonymous class' => ["<?php return new class {$body};", 'Anonymous'];

        yield 'class' => ["<?php class Foo {$body}"];

        yield 'enum' => ["<?php enum Foo {$body}"];

        yield 'trait' => ["<?php trait Foo {$body}"];
    }
}
