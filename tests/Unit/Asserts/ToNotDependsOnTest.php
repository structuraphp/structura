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
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOn(
                        names: [JsonSerializable::class],
                        patterns: ['Depend\Baz'],
                    ),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithNoDependsProvider')]
    public function testShouldFailToNotDependsOn(string $raw): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>Foo</promote> must not depends on these namespaces %s, %s, %s, [1+]',
                ArrayAccess::class,
                'Depend\Bar',
                Exception::class,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOn(
                        names: [
                            ArrayAccess::class,
                            Exception::class,
                            Stringable::class,
                        ],
                        patterns: ['Depend\(Bar|Baz)'],
                    ),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithNoDependsProvider(): Generator
    {
        yield 'class' => [
            <<<'PHP'
            <?php
            
            use ArrayAccess;
            use Depend\Bap;
            use Depend\Bar;
            
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
