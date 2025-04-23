<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Exception;
use Generator;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Stringable;
use Structura\Asserts\ToNotDependsOn;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependsOn::class)]
#[CoversMethod(Expr::class, 'toNotDependsOn')]
class ToNotDependsOnTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testToNotDependsOn(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toNotDependsOn([
                        JsonSerializable::class,
                    ]),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testShouldFailToNotDependsOn(string $raw): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>Foo</promote> must not depends on these namespaces %s, %s, %s',
                ArrayAccess::class,
                Exception::class,
                Stringable::class,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toNotDependsOn([
                        ArrayAccess::class,
                        Exception::class,
                        Stringable::class,
                    ]),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithNoDependsProvider(): Generator
    {
        yield 'class' => [
            <<<'PHP'
            <?php
            
            use ArrayAccess;
            
            class Foo implements \Stringable {
                public function __construct(ArrayAccess $arrayAccess) {
                    
                }

                public function __toString(): string {
                    return $this->arrayAccess['foo'] ?? throw new \Exception();
                }
            }
            PHP,
        ];
    }
}
