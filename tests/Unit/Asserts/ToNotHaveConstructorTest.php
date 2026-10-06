<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveMethod;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveMethod::class)]
#[CoversMethod(MethodAssert::class, 'toNotHaveConstructor')]
final class ToNotHaveConstructorTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToNotHaveConstructor(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { public function bar() {} }')
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotHaveConstructor(),
            );

        self::assertRulesPass(
            $rules,
            'to not have method <promote>__construct</promote>',
        );
    }

    public function testShouldFailToNotHaveConstructor(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw("<?php class Foo {\n    public function __construct() {}\n}")
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotHaveConstructor(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must not have method <fire>__construct</fire>',
            2,
        );
    }
}
