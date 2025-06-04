<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToUseDeclare::class)]
#[CoversMethod(Expr::class, 'toUseStrictTypes')]
final class ToUseStrictTypesTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToUserStrictType(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php declare(strict_types=1); class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRulesPass($rules);
    }

    public function testShouldFailToUserStrictType(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must use declaration <promote>strict_types=1</promote>',
        );
    }
}
