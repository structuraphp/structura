<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveConstant;
use StructuraPhp\Structura\Enums\VisibilityType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveConstant::class)]
#[CoversMethod(Expr::class, 'toHaveConstant')]
#[CoversMethod(Expr::class, 'toHavePublicConstant')]
#[CoversMethod(Expr::class, 'toHaveProtectedConstant')]
#[CoversMethod(Expr::class, 'toHavePrivateConstant')]
final class ToHaveConstantTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithPublicConstant')]
    public function testToHavePublicConstant(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveConstant(VisibilityType::Public),
            );

        self::assertRulesPass(
            $rules,
            'to have <promote>public</promote> constant',
        );
    }

    public static function getClassLikeWithPublicConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class { public const FOO = 1; };'];

        yield 'class' => ['<?php class Foo { public const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo { const BAR = 1; }'];

        yield 'interface' => ['<?php interface Foo { public const BAR = 1; }'];

        yield 'trait' => ['<?php trait Foo { public const BAR = 1; }'];
    }

    #[DataProvider('getClassLikeWithProtectedConstant')]
    public function testToHaveProtectedConstant(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveConstant(VisibilityType::Protected),
            );

        self::assertRulesPass(
            $rules,
            'to have <promote>protected</promote> constant',
        );
    }

    public static function getClassLikeWithProtectedConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class { protected const FOO = 1; };'];

        yield 'class' => ['<?php class Foo { protected const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo { protected const BAR = 1; }'];

        yield 'trait' => ['<?php trait Foo { protected const BAR = 1; }'];
    }

    #[DataProvider('getClassLikeWithPrivateConstant')]
    public function testToHavePrivateConstant(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveConstant(VisibilityType::Private),
            );

        self::assertRulesPass(
            $rules,
            'to have <promote>private</promote> constant',
        );
    }

    public static function getClassLikeWithPrivateConstant(): Generator
    {
        yield 'anonymous class' => ['<?php new class { private const FOO = 1; };'];

        yield 'class' => ['<?php class Foo { private const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo { private const BAR = 1; }'];

        yield 'trait' => ['<?php trait Foo { private const BAR = 1; }'];
    }

    public function testToHavePublicConstantViaShortcut(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { public const BAR = 1; }')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHavePublicConstant(),
            );

        self::assertRulesPass(
            $rules,
            'to have <promote>public</promote> constant',
        );
    }

    public function testToHaveProtectedConstantViaShortcut(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { protected const BAR = 1; }')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveProtectedConstant(),
            );

        self::assertRulesPass(
            $rules,
            'to have <promote>protected</promote> constant',
        );
    }

    public function testToHavePrivateConstantViaShortcut(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { private const BAR = 1; }')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHavePrivateConstant(),
            );

        self::assertRulesPass(
            $rules,
            'to have <promote>private</promote> constant',
        );
    }

    #[DataProvider('getClassLikeWithoutPublicConstant')]
    public function testShouldFailToHavePublicConstant(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveConstant(VisibilityType::Public),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have <promote>public</promote> constant',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithoutPublicConstant(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with private constant only' => ['<?php class Foo { private const BAR = 1; }'];

        yield 'class with protected constant only' => ['<?php class Foo { protected const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithoutProtectedConstant')]
    public function testShouldFailToHaveProtectedConstant(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveConstant(VisibilityType::Protected),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have <promote>protected</promote> constant',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithoutProtectedConstant(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with public constant only' => ['<?php class Foo { public const BAR = 1; }'];

        yield 'class with private constant only' => ['<?php class Foo { private const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithoutPrivateConstant')]
    public function testShouldFailToHavePrivateConstant(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveConstant(VisibilityType::Private),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have <promote>private</promote> constant',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithoutPrivateConstant(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with public constant only' => ['<?php class Foo { public const BAR = 1; }'];

        yield 'class with protected constant only' => ['<?php class Foo { protected const BAR = 1; }'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}
