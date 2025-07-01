<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Iterator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversMethod(Expr::class, 'and')]
final class AndTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToAnd(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo implements \ArrayAccess, \Iterator {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->and(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toImplement(ArrayAccess::class)
                            ->toImplement(Iterator::class),
                    ),
            );

        self::assertRulesPass(
            $rules,
            <<<TXT
            to implement <promote>ArrayAccess</promote>
               & to implement <promote>Iterator</promote>
            TXT
        );
    }

    public function testShouldFailToAnd(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo implements \ArrayAccess {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->and(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toImplement(ArrayAccess::class)
                            ->toImplement(Iterator::class),
                    ),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must implement <promote>ArrayAccess</promote>, '
            . 'Resource <promote>Foo</promote> must implement <promote>Iterator</promote>',
        );
    }
}
