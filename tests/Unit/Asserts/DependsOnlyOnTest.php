<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOn::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOn')]
final class DependsOnlyOnTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithDependsProvider')]
    public function testDependsOnlyOnWithClass(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOn(
                        names: [
                            ArrayAccess::class,
                            Exception::class,
                            Stringable::class,
                        ],
                        patterns: [
                            'Depend\(Bar|Bap)',
                            'Stri.+',
                        ],
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'depends only on these namespaces <promote>%s, %s, %s, [2+]</promote>',
                ArrayAccess::class,
                Exception::class,
                Stringable::class,
            ),
        );
    }

    #[DataProvider('getScriptWithDependsProvider')]
    public function testDependsOnlyOnWithScript(string $raw): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->dependsOnlyOn(
                        names: [
                            ArrayAccess::class,
                            Exception::class,
                            Stringable::class,
                        ],
                        patterns: [
                            'Depend\(Bar|Bap)',
                            'Stri.+',
                        ],
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'depends only on these namespaces <promote>%s, %s, %s, [2+]</promote>',
                ArrayAccess::class,
                Exception::class,
                Stringable::class,
            ),
        );
    }

    #[DataProvider('getClassLikeWithDependsProvider')]
    public function testShouldFailDependsOnlyOn(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOn(patterns: ['Depend\Bap']),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must depends only on these namespaces %s but depends %s, %s, %s, %s',
                'Depend\Bap',
                ArrayAccess::class,
                'Depend\Bar',
                Exception::class,
                Stringable::class,
            ),
        );
    }

    #[DataProvider('getScriptWithDependsProvider')]
    public function testShouldFailDependsOnlyOnWithScript(string $raw): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->dependsOnlyOn(patterns: ['Depend\Bap']),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must depends only on these namespaces %s but depends %s, %s, %s, %s',
                'Depend\Bap',
                ArrayAccess::class,
                'Depend\Bar',
                Exception::class,
                Stringable::class,
            ),
        );
    }

    public static function getClassLikeWithDependsProvider(): Generator
    {
        yield 'class' => [
            <<<'PHP'
            <?php
            
            use ArrayAccess;
            use Depend\Bap;
            use Depend\Bar;
            
            class Foo {
                public function __construct(ArrayAccess $arrayAccess) {
                    \Stringable::class;
                }

                public function __toString(): string {
                    return $this->arrayAccess['foo'] ?? throw new \Exception();
                }
            }
            PHP,
        ];
    }

    public static function getScriptWithDependsProvider(): Generator
    {
        yield 'script' => [
            <<<'PHP'
            <?php

            namespace Foo;

            use ArrayAccess;
            use Depend\Bap;
            use Depend\Bar;
            
            function foo(ArrayAccess $arrayAccess) {
                \Stringable::class;
            }

            function bar(): string {
                return $this->arrayAccess['foo'] ?? throw new \Exception();
            }
            PHP,
        ];
    }
}
