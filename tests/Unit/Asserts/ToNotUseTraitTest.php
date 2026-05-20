<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotUseTrait;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotUseTrait::class)]
#[CoversMethod(RelationAssert::class, 'toNotUseTrait')]
final class ToNotUseTraitTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testToUseNothing(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseTrait(),
            );

        self::assertRulesPass($rules, 'to not use trait');
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];
    }

    #[DataProvider('getClassLikeWithTrait')]
    public function testShouldFailToUseNothing(string $raw, string $exceptName): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseTrait(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not use a trait but uses <fire>%s</fire>',
                $exceptName,
                'HasFactory',
            ),
            2,
        );
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => [
            '<?php return new class { 
                use \HasFactory;
            };',
            'Anonymous',
        ];

        yield 'class' => [
            '<?php class Foo {
                use \HasFactory;
            }',
            'Foo',
        ];

        yield 'enum' => [
            '<?php enum Foo {
                use \HasFactory;
             };',
            'Foo',
        ];

        yield 'interface' => [
            '<?php interface Foo {
                use \HasFactory;
             }',
            'Foo',
        ];
    }

    public function testShouldFailWithMultipleTraits(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                '<?php class Foo {
                    use \TraitOne;
                    use \TraitTwo;
                }',
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseTrait(),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must not use a trait but uses <fire>TraitOne</fire>',
                'Resource <promote>Foo</promote> must not use a trait but uses <fire>TraitTwo</fire>',
            ],
            [2, 3],
        );
    }
}
