<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Generator;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use StructuraPhp\Structura\Asserts\ToOnlyImplement;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToOnlyImplement::class)]
#[CoversMethod(RelationAssert::class, 'toOnlyImplement')]
final class ToOnlyImplementTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithImplement')]
    public function testToOnlyImplement(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyImplement(Stringable::class),
            );

        self::assertRulesPass(
            $rules,
            'to only implement <promote>Stringable</promote>',
        );
    }

    public static function getClassLikeWithImplement(): Generator
    {
        yield 'anonymous class' => ['<?php new class implements \Stringable {};'];

        yield 'class' => ['<?php class Foo implements \Stringable {}'];

        yield 'enum' => ['<?php enum Foo implements \Stringable {};'];
    }

    #[DataProvider('getClassLikeWithoutImplement')]
    public function testShouldFailToOnlyImplement(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyImplement(Stringable::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must only implement <promote>%s</promote>',
                $exceptName,
                Stringable::class,
            ),
        );
    }

    public static function getClassLikeWithoutImplement(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];
    }

    #[DataProvider('getClassLikeWithMultipleImplement')]
    public function testShouldFailToOnlyImplementWithMultipleInterfaces(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyImplement(Stringable::class),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>%s</promote> must only implement <promote>%s</promote> but implement <fire>%s</fire>',
                    $exceptName,
                    Stringable::class,
                    Iterator::class,
                ),
                \sprintf(
                    'Resource <promote>%s</promote> must only implement <promote>%s</promote> but implement <fire>%s</fire>',
                    $exceptName,
                    Stringable::class,
                    ArrayAccess::class,
                ),
            ],
            [2, 3],
        );
    }

    public static function getClassLikeWithMultipleImplement(): Generator
    {
        yield 'class with multiple interfaces' => [
            '<?php class Foo implements \Stringable,
                \Iterator,
                \ArrayAccess {}',
        ];

        yield 'enum with multiple interfaces' => [
            '<?php enum Foo implements \Stringable,
                \Iterator,
                \ArrayAccess {};',
        ];
    }
}
