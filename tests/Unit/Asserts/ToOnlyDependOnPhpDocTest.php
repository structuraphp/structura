<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnPhpDoc;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToOnlyDependOnPhpDoc::class)]
#[CoversMethod(DependencyAssert::class, 'toOnlyDependOnPhpDoc')]
final class ToOnlyDependOnPhpDocTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getPassingCases')]
    public function testToOnlyDependOnPhpDocPasses(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyDependOnPhpDoc(
                        names: ArrayAccess::class,
                        patterns: 'Acme\.*',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to only depend on phpDoc <promote>ArrayAccess, Acme\.*</promote>',
        );
    }

    public static function getPassingCases(): Generator
    {
        yield 'no phpDoc' => ['<?php class Foo {}'];

        yield 'no phpDoc but has use' => ['<?php use ArrayAccess; class Foo {}'];

        yield 'phpDoc with allowed name via use' => [
            '<?php use ArrayAccess; class Foo { /** @param ArrayAccess $bar */ public function test(): void {} }',
        ];

        yield 'phpDoc with allowed pattern via use' => [
            '<?php namespace Acme; use Acme\Bar; class Foo { /** @param Bar $bar */ public function test(): void {} }',
        ];

        yield 'phpDoc with allowed name and pattern' => [
            '<?php use ArrayAccess; use Acme\Bar; class Foo { /** @param ArrayAccess $a @return Bar */ public function test(): void {} }',
        ];
    }

    #[DataProvider('getViolatingCases')]
    public function testToOnlyDependOnPhpDocViolates(string $raw, string $violation): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyDependOnPhpDoc(
                        names: ArrayAccess::class,
                        patterns: 'Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must only depend on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                ArrayAccess::class,
                'Acme\.*',
                $violation,
            ),
        );
    }

    public static function getViolatingCases(): Generator
    {
        yield 'phpDoc with forbidden class via use' => [
            '<?php use Forbidden\Bar; class Foo { /** @param Bar $bar */ public function test(): void {} }',
            'Forbidden\Bar',
        ];

        yield 'phpDoc with forbidden and allowed' => [
            '<?php use ArrayAccess; use Forbidden\Bar; class Foo { /** @param Bar $b @return ArrayAccess */ public function test(): void {} }',
            'Forbidden\Bar',
        ];
    }

    public function testToOnlyDependOnPhpDocViolatesWithMultipleForbidden(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                use Forbidden\Bar;
                use Forbidden\Baz;
                class Foo {
                    /**
                     * @param Bar $a
                     * @param Baz $b
                     */
                    public function test(): void {}
                }
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyDependOnPhpDoc(
                        names: ArrayAccess::class,
                        patterns: 'Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>Foo</promote> must only depend on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                    ArrayAccess::class,
                    'Acme\.*',
                    'Forbidden\Bar',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must only depend on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                    ArrayAccess::class,
                    'Acme\.*',
                    'Forbidden\Baz',
                ),
            ],
            [4, 4],
        );
    }

    public function testToOnlyDependOnPhpDocPassesViaPattern(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                '<?php use Acme\Bar; class Foo { /** @param Bar $bar */ public function test(): void {} }',
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyDependOnPhpDoc(
                        names: ArrayAccess::class,
                        patterns: 'Acme\.*',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to only depend on phpDoc <promote>ArrayAccess, Acme\.*</promote>',
        );
    }
}
