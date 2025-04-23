<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Stringable;
use Structura\Asserts\DependsOnlyOn;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOn::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOn')]
class DependsOnlyOnTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithDependsProvider')]
    public function testDependsOnlyOn(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOn([
                        ArrayAccess::class,
                        Exception::class,
                        Stringable::class,
                    ]),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithDependsProvider')]
    public function testShouldFailDependsOnlyOn(string $raw): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>Foo</promote> must depends only on these namespaces %s, %s, %s',
                ArrayAccess::class,
                Exception::class,
                Stringable::class,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOn([]),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithDependsProvider(): Generator
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
