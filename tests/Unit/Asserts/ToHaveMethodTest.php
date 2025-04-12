<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

class ToHaveMethodTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithMethod')]
    public function testToHaveMethod(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toHaveMethod('bar'),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithoutMethod')]
    public function testShouldFailToHaveMethod(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must have method <promote>bar</promote>',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toHaveMethod('bar'),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithoutMethod(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {}'];
        yield 'interface' => ['<?php interface Foo {}'];
        yield 'trait' => ['<?php trait Foo {}'];
    }

    public static function getClassLikeWithMethod(): Generator
    {
        yield 'anonymous class' => ['<?php new class { public function bar() {} };'];
        yield 'class' => ['<?php class Foo { public function bar() {} }'];
        yield 'enum' => ['<?php enum Foo { public function bar() {} }'];
        yield 'interface' => ['<?php interface Foo { public function bar(); }'];
        yield 'trait' => ['<?php trait Foo { public function bar() {} }'];
    }
}
