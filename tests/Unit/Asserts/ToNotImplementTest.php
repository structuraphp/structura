<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Generator;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotImplement;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotImplement::class)]
#[CoversMethod(RelationAssert::class, 'toNotImplement')]
final class ToNotImplementTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeNotImplementingArrayAccess')]
    public function testToNotImplement(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotImplement(ArrayAccess::class),
            );

        self::assertRulesPass(
            $rules,
            'to not implement <promote>ArrayAccess</promote>',
        );
    }

    public static function getClassLikeNotImplementingArrayAccess(): Generator
    {
        yield 'anonymous class' => ['<?php return new class implements \Countable {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class implementing another interface' => ['<?php class Foo implements \Countable {}'];

        yield 'enum' => ['<?php enum Foo implements \Countable {}'];

        yield 'interface' => ['<?php interface Foo extends \ArrayAccess {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeImplementingArrayAccess')]
    public function testShouldFailToNotImplement(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotImplement(ArrayAccess::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not implement <fire>ArrayAccess</fire>',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeImplementingArrayAccess(): Generator
    {
        yield 'anonymous class' => ['<?php return new class implements \ArrayAccess {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo implements \ArrayAccess {}'];

        yield 'enum' => ['<?php enum Foo implements \ArrayAccess {}'];
    }

    public function testShouldFailToNotImplementWithMultipleViolations(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo implements \ArrayAccess, \Countable, \JsonSerializable {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotImplement([ArrayAccess::class, JsonSerializable::class]),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must not implement <fire>ArrayAccess</fire>',
                'Resource <promote>Foo</promote> must not implement <fire>JsonSerializable</fire>',
            ],
            [1, 1],
        );
    }
}
