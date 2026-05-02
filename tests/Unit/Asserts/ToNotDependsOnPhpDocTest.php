<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotDependsOnPhpDoc;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependsOnPhpDoc::class)]
#[CoversMethod(ExprScript::class, 'toNotDependsOnPhpDoc')]
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
}
