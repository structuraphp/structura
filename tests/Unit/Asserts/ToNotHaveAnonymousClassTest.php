<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveAnonymousClass;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveAnonymousClass::class)]
#[CoversMethod(Expr::class, 'toNotHaveAnonymousClass')]
#[CoversMethod(ExprScript::class, 'toNotHaveAnonymousClass')]
final class ToNotHaveAnonymousClassTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassWithoutAnonymousProvider')]
    public function testToNotHaveAnonymousClassWithClass(string $rawClass): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveAnonymousClass(),
            );

        self::assertRulesPass(
            $rules,
            'to not have anonymous class',
        );
    }

    public static function getClassWithoutAnonymousProvider(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getScriptWithoutAnonymousProvider')]
    public function testToNotHaveAnonymousClassWithScript(string $rawScript): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotHaveAnonymousClass(),
            );

        self::assertRulesPass(
            $rules,
            'to not have anonymous class',
        );
    }

    public static function getScriptWithoutAnonymousProvider(): Generator
    {
        yield 'script without anonymous class' => [
            '<?php echo "hello";',
        ];

        yield 'script with function' => [
            '<?php function foo() {}',
        ];
    }

    #[DataProvider('getClassWithAnonymousProvider')]
    public function testShouldFailToNotHaveAnonymousClassWithClass(
        string $rawClass,
        string $name,
        int $count,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveAnonymousClass(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have anonymous class',
                $name,
            ),
            3,
        );
    }

    public static function getClassWithAnonymousProvider(): Generator
    {
        yield 'class with one anonymous class' => [
            '<?php class Foo { 
                public function bar() {
                    return new class {};
                }
            }',
            'Foo',
            1,
        ];

        yield 'enum with anonymous class' => [
            '<?php enum Foo {
                public function bar() {
                    return new class {};
                }
            }',
            'Foo',
            1,
        ];
    }

    #[DataProvider('getClassWithAnonymousMultipleProvider')]
    public function testShouldFailToNotHaveAnonymousClassWithMultipleClass(
        string $rawClass,
        string $name,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveAnonymousClass(),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>%s</promote> must not have anonymous class',
                    $name,
                ),
                \sprintf(
                    'Resource <promote>%s</promote> must not have anonymous class',
                    $name,
                ),
            ],
            [3, 7],
        );
    }

    public static function getClassWithAnonymousMultipleProvider(): Generator
    {
        yield 'class with two anonymous classes' => [
            '<?php class Foo {
                        public function bar() {
                            return new class {};
                        }

                        public function baz() {
                            return new class {};
                        }
                    }',
            'Foo',
        ];

        yield 'class with nested anonymous class' => [
            '<?php class Foo {
                        public function bar() {
                            return new class {
                                public function baz() {
                                
                                
                                    return new class {};
                                }
                            };
                        }
                    }',
            'Foo',
        ];
    }

    #[DataProvider('getScriptWithAnonymousProvider')]
    public function testShouldFailToNotHaveAnonymousClassWithScript(
        string $rawScript,
        int $count,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotHaveAnonymousClass(),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>tmp/run_0.php</promote> must not have anonymous class',
                'Resource <promote>tmp/run_0.php</promote> must not have anonymous class',
            ],
            $count,
        );
    }

    public static function getScriptWithAnonymousProvider(): Generator
    {
        /*  yield 'script with one anonymous class' => [
              '<?php
              $obj = new class {};',
              1,
          ];*/

        yield 'script with two anonymous classes' => [
            '<?php
            $obj1 = new class {};
            $obj2 = new class {};',
            2,
        ];

        yield 'script with nested anonymous class' => [
            '<?php
            $obj = new class {
                public function bar() { return new class {}; }
            };',
            2,
        ];
    }
}
