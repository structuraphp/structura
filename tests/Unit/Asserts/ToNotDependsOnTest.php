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
use StructuraPhp\Structura\Asserts\ToNotDependsOn;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependsOn::class)]
#[CoversMethod(Expr::class, 'toNotDependsOn')]
final class ToNotDependsOnTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testToNotDependsOnWithClass(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOn(
                        names: [JsonSerializable::class],
                        patterns: ['Depend\Baz'],
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to not depends on these namespaces <promote>%s, %s</promote>',
                JsonSerializable::class,
                'Depend\Baz',
            ),
        );
    }

    #[DataProvider('getScriptWithNoDependsProvider')]
    public function testToNotDependsOnWithScript(string $raw): void
    {
        $rules = $this
            ->allScript()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotDependsOn(
                        names: [JsonSerializable::class],
                        patterns: ['Depend\Baz'],
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to not depends on these namespaces <promote>%s, %s</promote>',
                JsonSerializable::class,
                'Depend\Baz',
            ),
        );
    }

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testShouldFailToNotDependsOnWithClass(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOn(
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
            \sprintf(
                'Resource <promote>Foo</promote> must not depends on these namespaces %s, %s, %s, [1+]',
                ArrayAccess::class,
                'Depend\Bar',
                Exception::class,
            ),
        );
    }

    #[DataProvider('getScriptWithNoDependsProvider')]
    public function testShouldFailToNotDependsOnWithScript(string $raw): void
    {
        $rules = $this
            ->allScript()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotDependsOn(
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
            \sprintf(
                'Resource <promote>Foo</promote> must not depends on these namespaces %s, %s, %s, [1+]',
                ArrayAccess::class,
                'Depend\Bar',
                Exception::class,
            ),
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

    public static function getScriptWithNoDependsProvider(): Generator
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
