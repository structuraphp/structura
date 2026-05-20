<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use StructuraPhp\Structura\Asserts\ToNotDependsOnPhpDoc;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependsOnPhpDoc::class)]
#[CoversMethod(DependencyAssert::class, 'toNotDependsOnPhpDoc')]
final class ToNotDependsOnPhpDocTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getPassingCases')]
    public function testToNotDependsOnPhpDocPasses(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnPhpDoc(
                        names: ArrayAccess::class,
                        patterns: 'Forbidden\.*',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to not depends on phpDoc <promote>ArrayAccess, Forbidden\.*</promote>',
        );
    }

    public static function getPassingCases(): Generator
    {
        yield 'no phpDoc' => ['<?php class Foo {}'];

        yield 'no phpDoc but has use' => ['<?php use ArrayAccess; class Foo {}'];

        yield 'phpDoc with different name' => [
            '<?php use Acme\Bar; class Foo { /** @param Bar $bar */ public function test(): void {} }',
        ];
    }

    #[DataProvider('getViolatingCases')]
    public function testToNotDependsOnPhpDocViolates(string $raw, string $violation): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnPhpDoc(
                        names: ArrayAccess::class,
                        patterns: 'Forbidden\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must not depends on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                ArrayAccess::class,
                'Forbidden\.*',
                $violation,
            ),
        );
    }

    public static function getViolatingCases(): Generator
    {
        yield 'phpDoc with forbidden name' => [
            '<?php use ArrayAccess; class Foo { /** @param ArrayAccess $bar */ public function test(): void {} }',
            'ArrayAccess',
        ];

        yield 'phpDoc with forbidden pattern' => [
            '<?php use Forbidden\Bar; class Foo { /** @param Bar $bar */ public function test(): void {} }',
            'Forbidden\Bar',
        ];
    }

    public function testToNotDependsOnPhpDocOnlyViolatesIntersection(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                use ArrayAccess;
                class Foo {
                    /** @param ArrayAccess $bar */
                    public function test($bar): void {}
                }
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnPhpDoc(
                        names: [ArrayAccess::class, Stringable::class],
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must not depends on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                ArrayAccess::class,
                Stringable::class,
                ArrayAccess::class,
            ),
            3,
        );
    }

    public function testToNotDependsOnPhpDocViolationsAreSorted(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                use Forbidden\Zzz;
                use Forbidden\Aaa;
                class Foo {
                    /**
                     * @param Zzz $z
                     * @param Aaa $a
                     */
                    public function test(): void {}
                }
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnPhpDoc(
                        patterns: 'Forbidden\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>Foo</promote> must not depends on these phpDoc namespaces %s but depends on <fire>%s</fire>',
                    'Forbidden\.*',
                    'Forbidden\Aaa',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must not depends on these phpDoc namespaces %s but depends on <fire>%s</fire>',
                    'Forbidden\.*',
                    'Forbidden\Zzz',
                ),
            ],
            [4, 4],
        );
    }

    public function testToNotDependsOnPhpDocMultipleViolations(): void
    {
        // Deux dépendances interdites en phpdoc (tue ArrayOneItem)
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                use ArrayAccess;
                use Forbidden\Bar;
                class Foo {
                    /**
                     * @param ArrayAccess $a
                     * @param Bar $b
                     */
                    public function test(): void {}
                }
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnPhpDoc(
                        names: [ArrayAccess::class],
                        patterns: 'Forbidden\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>Foo</promote> must not depends on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                    ArrayAccess::class,
                    'Forbidden\.*',
                    ArrayAccess::class,
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must not depends on these phpDoc namespaces %s, %s but depends on <fire>%s</fire>',
                    ArrayAccess::class,
                    'Forbidden\.*',
                    'Forbidden\Bar',
                ),
            ],
            [4, 4],
        );
    }
}
