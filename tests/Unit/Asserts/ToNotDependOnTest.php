<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Exception;
use Generator;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use StructuraPhp\Structura\Asserts\ToNotDependOn;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependOn::class)]
#[CoversMethod(DependencyAssert::class, 'toNotDependOn')]
final class ToNotDependOnTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testToNotDependOnWithClass(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOn(
                        names: [JsonSerializable::class],
                        patterns: ['Depend\Baz'],
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to not depend on these namespaces <promote>%s, %s</promote>',
                JsonSerializable::class,
                'Depend\Baz',
            ),
        );
    }

    #[DataProvider('getScriptWithNoDependsProvider')]
    public function testToNotDependOnWithScript(string $raw, string $exceptName): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotDependOn(
                        names: [JsonSerializable::class],
                        patterns: ['Depend\Baz'],
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to not depend on these namespaces <promote>%s, %s</promote>',
                JsonSerializable::class,
                'Depend\Baz',
            ),
        );
    }

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testShouldFailToNotDependOnWithClass(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOn(
                        names: [
                            ArrayAccess::class,
                            Exception::class,
                            Stringable::class,
                        ],
                        patterns: ['Depend\(Bar|Baz)'],
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>Foo</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'ArrayAccess',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'Depend\Bar',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'Exception',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'Stringable',
                ),
            ],
            [7, 7, 7, 7],
        );
    }

    public static function getClassLikeWithNoDependsProvider(): Generator
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

    #[DataProvider('getScriptWithNoDependsProvider')]
    public function testShouldFailToNotDependOnWithScript(
        string $raw,
        string $exceptName,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotDependOn(
                        names: [
                            ArrayAccess::class,
                            Exception::class,
                            Stringable::class,
                        ],
                        patterns: ['Depend\(Bar|Baz)'],
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                sprintf(
                    'Resource <promote>%s</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'ArrayAccess',
                ),
                sprintf(
                    'Resource <promote>%s</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'Depend\Bar',
                ),
                sprintf(
                    'Resource <promote>%s</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'Exception',
                ),
                sprintf(
                    'Resource <promote>%s</promote> must not depend on these namespaces %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'ArrayAccess, Exception, Stringable, Depend\(Bar|Baz)',
                    'Stringable',
                ),
            ],
            0,
        );
    }

    public static function getScriptWithNoDependsProvider(): Generator
    {
        yield 'script with namespace' => [
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
            'Foo',
        ];

        yield 'script without namespace' => [
            <<<'PHP'
            <?php

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
            'tmp/run_0.php',
        ];
    }

    public function testShouldFailToNotDependOnOnlyIntersection(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                use ArrayAccess;
                class Foo {
                    public function __construct(ArrayAccess $a) {}
                }
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOn(
                        names: [ArrayAccess::class, Stringable::class],
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must not depend on these namespaces %s, %s but depends on <fire>%s</fire>',
                ArrayAccess::class,
                Stringable::class,
                ArrayAccess::class,
            ),
            3,
        );
    }
}
