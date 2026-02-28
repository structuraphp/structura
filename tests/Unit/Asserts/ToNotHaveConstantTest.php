<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveConstant;
use StructuraPhp\Structura\Enums\VisibilityType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveConstant::class)]
#[CoversMethod(Expr::class, 'toNotHaveConstant')]
#[CoversMethod(Expr::class, 'toNotHavePublicConstant')]
#[CoversMethod(Expr::class, 'toNotHaveProtectedConstant')]
#[CoversMethod(Expr::class, 'toNotHavePrivateConstant')]
final class ToNotHaveConstantTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutPublicConstant')]
    public function testToNotHavePublicConstant(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveConstant(VisibilityType::Public),
            );

        self::assertRulesPass(
            $rules,
            'to not have <promote>public</promote> constant',
        );
    }

    public static function getClassLikeWithoutPublicConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with private constant only' => ['<?php class Foo { private const BAR = 1; }'];

        yield 'class with protected constant only' => ['<?php class Foo { protected const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithoutProtectedConstant')]
    public function testToNotHaveProtectedConstant(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveConstant(VisibilityType::Protected),
            );

        self::assertRulesPass(
            $rules,
            'to not have <promote>protected</promote> constant',
        );
    }

    public static function getClassLikeWithoutProtectedConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with public constant only' => ['<?php class Foo { public const BAR = 1; }'];

        yield 'class with private constant only' => ['<?php class Foo { private const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithoutPrivateConstant')]
    public function testToNotHavePrivateConstant(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveConstant(VisibilityType::Private),
            );

        self::assertRulesPass(
            $rules,
            'to not have <promote>private</promote> constant',
        );
    }

    public static function getClassLikeWithoutPrivateConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with public constant only' => ['<?php class Foo { public const BAR = 1; }'];

        yield 'class with protected constant only' => ['<?php class Foo { protected const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    public function testToNotHavePublicConstantViaShortcut(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHavePublicConstant(),
            );

        self::assertRulesPass(
            $rules,
            'to not have <promote>public</promote> constant',
        );
    }

    public function testToNotHaveProtectedConstantViaShortcut(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveProtectedConstant(),
            );

        self::assertRulesPass(
            $rules,
            'to not have <promote>protected</promote> constant',
        );
    }

    public function testToNotHavePrivateConstantViaShortcut(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHavePrivateConstant(),
            );

        self::assertRulesPass(
            $rules,
            'to not have <promote>private</promote> constant',
        );
    }

    #[DataProvider('getClassLikeWithPublicConstant')]
    public function testShouldFailToNotHavePublicConstant(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveConstant(VisibilityType::Public),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have <promote>public</promote> constant',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithPublicConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class { public const FOO = 1; };', 'Anonymous'];

        yield 'class' => ['<?php class Foo { public const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo { const BAR = 1; }'];

        yield 'interface' => ['<?php interface Foo { public const BAR = 1; }'];

        yield 'trait' => ['<?php trait Foo { public const BAR = 1; }'];
    }

    #[DataProvider('getClassLikeWithProtectedConstant')]
    public function testShouldFailToNotHaveProtectedConstant(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveConstant(VisibilityType::Protected),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have <promote>protected</promote> constant',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithProtectedConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class { protected const FOO = 1; };', 'Anonymous'];

        yield 'class' => ['<?php class Foo { protected const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo { protected const BAR = 1; }'];

        yield 'trait' => ['<?php trait Foo { protected const BAR = 1; }'];
    }

    #[DataProvider('getClassLikeWithPrivateConstant')]
    public function testShouldFailToNotHavePrivateConstant(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveConstant(VisibilityType::Private),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have <promote>private</promote> constant',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithPrivateConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class { private const FOO = 1; };', 'Anonymous'];

        yield 'class' => ['<?php class Foo { private const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo { private const BAR = 1; }'];

        yield 'trait' => ['<?php trait Foo { private const BAR = 1; }'];
    }
}
