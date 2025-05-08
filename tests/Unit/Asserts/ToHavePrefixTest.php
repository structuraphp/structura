<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveMethod::class)]
#[CoversMethod(Expr::class, 'toHavePrefix')]
final class ToHavePrefixTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithPrefixProvider')]
    public function testToHavePrefix(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHavePrefix('Controller'),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithoutPrefixProvider')]
    public function testShouldFailToHavePrefix(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource name <promote>%s</promote> must start with <promote>Controller</promote>',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHavePrefix('Controller'),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithPrefixProvider(): Generator
    {
        yield 'class' => ['<?php class ControllerFoo {}'];

        yield 'enum' => ['<?php enum ControllerFoo {}'];

        yield 'interface' => ['<?php interface ControllerFoo {}'];

        yield 'trait' => ['<?php trait ControllerFoo {}'];
    }

    public static function getClassLikeWithoutPrefixProvider(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}
