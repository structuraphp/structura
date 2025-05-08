<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use AppendIterator;
use ArrayIterator;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversMethod(Expr::class, 'or')]
final class OrTest extends TestCase
{
    use ArchitectureAsserts;

    public function testShouldOr(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir('tests/Fixture/Exceptions')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(InvalidArgumentException::class)
                            ->toExtend(Exception::class),
                    ),
            );

        self::assertRules($rules);
    }

    public function testShouldFailToOr(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $rules = $this
            ->allClasses()
            ->fromDir('tests/Fixture/Exceptions')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(ArrayIterator::class)
                            ->toExtend(AppendIterator::class),
                    ),
            );

        self::assertRules($rules);
    }
}
