<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveMethod;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveMethod::class)]
#[CoversMethod(TypeAssert::class, 'toNotBeInvokable')]
final class ToNotBeInvokableTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToNotBeInvokable(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { public function bar() {} }')
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotBeInvokable(),
            );

        self::assertRulesPass(
            $rules,
            'to not have method <promote>__invoke</promote>',
        );
    }

    public function testShouldFailToNotBeInvokable(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw("<?php class Foo {\n    public function __invoke() {}\n}")
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotBeInvokable(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must not have method <fire>__invoke</fire>',
            2,
        );
    }
}
