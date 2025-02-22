<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Stringable;
use Structura\Expr;
use Structura\Testing\ArchitectureAsserts;

class ToImplementTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithImplement')]
    public function testToImplement(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toImplement(Stringable::class),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithoutImplement')]
    public function testShouldFailToImplement(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must implement <promote>%s</promote>',
                $exceptName,
                Stringable::class,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toImplement(Stringable::class),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithImplement(): Generator
    {
        yield 'anonymous class' => ['<?php new class implements \Stringable {};'];
        yield 'class' => ['<?php class Foo implements \Stringable {}'];
        yield 'enum' => ['<?php enum Foo implements \Stringable {};'];
    }

    public static function getClassLikeWithoutImplement(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {};'];
        yield 'interface' => ['<?php interface Foo {}'];
    }
}
