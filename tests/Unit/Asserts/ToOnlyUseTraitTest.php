<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToOnlyUseTrait;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToOnlyUseTrait::class)]
#[CoversMethod(RelationAssert::class, 'toOnlyUseTrait')]
final class ToOnlyUseTraitTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithTrait')]
    public function testToOnlyUse(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyUseTrait(HasFactory::class),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to only use trait <promote>%s</promote>',
                HasFactory::class,
            ),
        );
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => [
            '<?php return new class { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; };',
        ];

        yield 'class' => [
            '<?php class Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; }',
        ];

        yield 'enum' => [
            '<?php enum Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; };',
        ];

        yield 'interface' => [
            '<?php interface Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; }',
        ];
    }

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testShouldFailToOnlyUse(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyUseTrait(HasFactory::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> should only use trait <promote>%s</promote>',
                $exceptName,
                HasFactory::class,
            ),
        );
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];
    }

    #[DataProvider('getClassLikeWithMultipleTraits')]
    public function testShouldFailToOnlyUseWithMultipleTraits(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyUseTrait(HasFactory::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> should only use trait <promote>%s</promote> but uses <fire>%s</fire>',
                $exceptName,
                HasFactory::class,
                'OtherTrait',
            ),
            4,
        );
    }

    public static function getClassLikeWithMultipleTraits(): Generator
    {
        yield 'class with multiple traits' => [
            '<?php
             class Foo { 
                use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory,
                OtherTrait;
            }',
        ];

        yield 'enum with multiple traits' => [
            '<?php
             enum Foo { 
                use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory,
                OtherTrait;
            }',
        ];
    }

    public function testShouldFailToOnlyUseWithTwoForbiddenTraits(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                '<?php class Foo { use \BadTraitOne; use \BadTraitTwo; }',
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyUseTrait(HasFactory::class),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>Foo</promote> should only use trait <promote>%s</promote> but uses <fire>%s</fire>',
                    HasFactory::class,
                    'BadTraitOne',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> should only use trait <promote>%s</promote> but uses <fire>%s</fire>',
                    HasFactory::class,
                    'BadTraitTwo',
                ),
            ],
            [1, 1],
        );
    }
}
