<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveAnonymousClass;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveAnonymousClass::class)]
#[CoversMethod(Expr::class, 'toHaveAnonymousClass')]
#[CoversMethod(ExprScript::class, 'toHaveAnonymousClass')]
final class ToHaveAnonymousClassTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassWithAnonymousProvider')]
    public function testToHaveAnonymousClassWithClass(string $rawClass): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveAnonymousClass(),
            );

        self::assertRulesPass(
            $rules,
            'to have anonymous class',
        );
    }

    public static function getClassWithAnonymousProvider(): Generator
    {
        yield 'class with anonymous class in method' => [
            '<?php class Foo { public function bar() { return new class {}; } }',
        ];

        yield 'class with multiple anonymous classes' => [
            '<?php class Foo { 
                public function bar() { return new class {}; } 
                public function baz() { return new class {}; } 
            }',
        ];

        yield 'class with nested anonymous class' => [
            '<?php class Foo { 
                public function bar() { 
                    return new class { 
                        public function baz() { return new class {}; } 
                    }; 
                } 
            }',
        ];

        yield 'enum with anonymous class' => [
            '<?php enum Foo { public function bar() { return new class {}; } }',
        ];
    }

    #[DataProvider('getScriptWithAnonymousProvider')]
    public function testToHaveAnonymousClassWithScript(string $rawScript): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toHaveAnonymousClass(),
            );

        self::assertRulesPass(
            $rules,
            'to have anonymous class',
        );
    }

    public static function getScriptWithAnonymousProvider(): Generator
    {
        yield 'script with anonymous class' => [
            '<?php $obj = new class {};',
        ];

        yield 'script with multiple anonymous classes' => [
            '<?php 
            $obj1 = new class {}; 
            $obj2 = new class {};',
        ];

        yield 'script with nested anonymous class' => [
            '<?php $obj = new class { 
                public function bar() { return new class {}; } 
            };',
        ];
    }

    #[DataProvider('getClassWithoutAnonymousProvider')]
    public function testShouldFailToHaveAnonymousClassWithClass(
        string $rawClass,
        string $name,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveAnonymousClass(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have anonymous class',
                $name,
            ),
        );
    }

    public static function getClassWithoutAnonymousProvider(): Generator
    {
        yield 'class without anonymous class' => [
            '<?php class Foo {}',
            'Foo',
        ];

        yield 'enum without anonymous class' => [
            '<?php enum Foo {}',
            'Foo',
        ];

        yield 'interface without anonymous class' => [
            '<?php interface Foo {}',
            'Foo',
        ];

        yield 'trait without anonymous class' => [
            '<?php trait Foo {}',
            'Foo',
        ];
    }

    #[DataProvider('getScriptWithoutAnonymousProvider')]
    public function testShouldFailToHaveAnonymousClassWithScript(string $rawScript): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toHaveAnonymousClass(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>tmp/run_0.php</promote> must have anonymous class',
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
}
