<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Testing\ArchitectureAsserts;

class AndTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToAnd(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo implements \ArrayAccess, \Iterator {}')
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->and(
                        static fn(Expr $assertion): Expr => $assertion
                            ->toImplement(ArrayAccess::class)
                            ->toImplement(Iterator::class),
                    ),
            );

        self::assertRules($rules);
    }

    public function testShouldFailToAnd(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo implements \ArrayAccess {}')
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->and(
                        static fn(Expr $assertion): Expr => $assertion
                            ->toImplement(ArrayAccess::class)
                            ->toImplement(Iterator::class),
                    ),
            );

        self::assertRules($rules);
    }
}
