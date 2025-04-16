<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependsOn::class)]
#[CoversMethod(Expr::class, 'toNotDependsOn')]
final class ToNotDependsOn extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithImplement')]
    public function testToImplement(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toNotDependsOn([
                        Exception::class,
                    ]),
            );

        self::assertRules($rules);
    }


    public static function getClassLikeWithImplement(): Generator
    {
        yield 'class' => [
            <<<'PHP'
            <?php
            
            use ArrayAccess;
            
            class Foo implements \Stringable {
                public function __construct(ArrayAccess $arrayAccess) {
                    
                }
            }
            PHP,
        ];
    }
}
