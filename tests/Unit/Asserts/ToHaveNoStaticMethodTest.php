<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveNoStaticMethod;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveNoStaticMethod::class)]
#[CoversMethod(MethodAssert::class, 'toHaveNoStaticMethod')]
final class ToHaveNoStaticMethodTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutStaticMethod')]
    public function testToHaveNoStaticMethod(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveNoStaticMethod(),
            );

        self::assertRulesPass($rules, 'to have no static method');
    }

    public static function getClassLikeWithoutStaticMethod(): Generator
    {
        yield 'no method' => ['<?php class Foo {}'];

        yield 'instance methods' => ['<?php class Foo { public function __invoke() {} private function bar() {} }'];

        yield 'static property and static closure' => [
            '<?php class Foo { private static int $count = 0; public function bar() { return static fn () => 1; } }',
        ];

        yield 'anonymous class' => ['<?php return new class { public function bar() {} };'];

        yield 'interface' => ['<?php interface Foo { public function bar(); }'];
    }

    #[DataProvider('getClassLikeWithStaticMethod')]
    public function testShouldFailToHaveNoStaticMethod(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveNoStaticMethod(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have no static method but has <fire>make</fire>',
                $exceptName,
            ),
            3,
        );
    }

    public static function getClassLikeWithStaticMethod(): Generator
    {
        $body = "{\n    public function __invoke() {}\n    private static function make() {}\n}";

        yield 'class' => ["<?php class Foo {$body}"];

        yield 'anonymous class' => ["<?php return new class {$body};", 'Anonymous'];

        yield 'enum' => ["<?php enum Foo {$body}"];

        yield 'trait' => ["<?php trait Foo {$body}"];

        yield 'interface' => ["<?php interface Foo {\n    public function __invoke();\n    public static function make();\n}"];
    }

    public function testShouldFailToHaveNoStaticMethodWithMultipleViolations(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw("<?php class Foo {\n    public static function make() {}\n    public static function refresh() {}\n}")
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveNoStaticMethod(),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must have no static method but has <fire>make</fire>',
                'Resource <promote>Foo</promote> must have no static method but has <fire>refresh</fire>',
            ],
            [2, 3],
        );
    }
}
