<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveOnlyPublicMethods;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveOnlyPublicMethods::class)]
#[CoversMethod(MethodAssert::class, 'toHaveOnlyPublicMethods')]
final class ToHaveOnlyPublicMethodsTest extends TestCase
{
    use ArchitectureAsserts;

    private const LABEL = 'to have only public methods <promote>__construct, __invoke</promote>';

    #[DataProvider('getClassLikeWithAllowedPublicMethods')]
    public function testToHaveOnlyPublicMethods(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveOnlyPublicMethods(['__construct', '__invoke']),
            );

        self::assertRulesPass($rules, self::LABEL);
    }

    public static function getClassLikeWithAllowedPublicMethods(): Generator
    {
        yield 'no method' => ['<?php class Foo {}'];

        yield 'allowed public methods' => [
            '<?php class Foo { public function __construct() {} public function __invoke() {} }',
        ];

        yield 'private and protected methods' => [
            '<?php class Foo { public function __invoke() {} private function bar() {} protected function baz() {} }',
        ];

        yield 'case-insensitive name' => ['<?php class Foo { public function __INVOKE() {} }'];

        yield 'anonymous class' => ['<?php return new class { private function bar() {} };'];
    }

    public function testToHaveOnlyPublicMethodsIgnoresCaseOfAllowedNames(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { public function handle() {} }')
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPublicMethods(['Handle']),
            );

        self::assertRulesPass($rules, 'to have only public methods <promote>Handle</promote>');
    }

    #[DataProvider('getClassLikeWithForbiddenPublicMethod')]
    public function testShouldFailToHaveOnlyPublicMethods(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveOnlyPublicMethods(['__construct', '__invoke']),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have only public methods <promote>__construct, __invoke</promote> but has public method <fire>bar</fire>',
                $exceptName,
            ),
            2,
        );
    }

    public static function getClassLikeWithForbiddenPublicMethod(): Generator
    {
        $body = "{\n    public function bar() {}\n    private function baz() {}\n}";

        yield 'implicit public method' => ["<?php class Foo {\n    function bar() {}\n}"];

        yield 'class' => ["<?php class Foo {$body}"];

        yield 'anonymous class' => ["<?php return new class {$body};", 'Anonymous'];

        yield 'enum' => ["<?php enum Foo {$body}"];

        yield 'trait' => ["<?php trait Foo {$body}"];
    }

    public function testShouldFailToHaveOnlyPublicMethodsWithMultipleViolations(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw("<?php class Foo {\n    public function bar() {}\n    public static function make() {}\n}")
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveOnlyPublicMethods(['__construct', '__invoke']),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must have only public methods <promote>__construct, __invoke</promote> but has public method <fire>bar</fire>',
                'Resource <promote>Foo</promote> must have only public methods <promote>__construct, __invoke</promote> but has public method <fire>make</fire>',
            ],
            [2, 3],
        );
    }
}
