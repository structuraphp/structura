<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(Expr::class)]
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
                static fn(Expr $assert): Expr => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRules($rules);
    }

    public function testShouldFailToUserStrictType(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            'Resource <promote>Foo</promote> must use declaration <promote>strict_types=1</promote>',
        );

        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRules($rules);
    }
}
