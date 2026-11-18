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
use StructuraPhp\Structura\ValueObjects\ScriptDescription;

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
            [5, 5],
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

    /**
     * @param array<int, class-string> $names
     * @param array<int, string> $patterns
     */
    #[DataProvider('getNamesOrPatternsOnlyProvider')]
    public function testShouldFailToOnlyDependOnPhpDocNamesOrPatternsOnly(array $names, array $patterns): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(<<<'PHP'
            <?php
            use ArrayAccess;
            use Countable;
            class Foo {
                /**
                 * @param ArrayAccess $bar
                 * @param Countable $baz
                 */
                public function test($bar, $baz) {}
            }
            PHP)
            ->should(static fn (Expr $assert): Expr => $assert->toOnlyDependOnPhpDoc($names, $patterns));

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must only depend on these phpDoc namespaces ArrayAccess but depends on <fire>Countable</fire>',
            5,
        );
    }

    /**
     * @return Generator<string, array{array<int, class-string>, array<int, string>}>
     */
    public static function getNamesOrPatternsOnlyProvider(): Generator
    {
        yield 'names only' => [[ArrayAccess::class], []];

        yield 'patterns only' => [[], ['ArrayAccess']];
    }

    public function testScriptViolationIsOnDependencyLine(): void
    {
        $description = (new ScriptDescription(null, null, [], [], null))
            ->setDocBlockDependencies(['Countable' => 7]);

        $violations = (new ToOnlyDependOnPhpDoc([ArrayAccess::class], []))->getViolation($description);

        self::assertSame(7, $violations[0]->line);
    }
}
