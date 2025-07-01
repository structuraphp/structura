<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotDependsOnFunction;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependsOnFunction::class)]
#[CoversMethod(Expr::class, 'toNotDependsOnFunction')]
class NotDependsOnFunctionTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithFunction')]
    public function testNotDependsOnFunction(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnFunction(
                        names: 'strtoupper',
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'not depends on function <promote>strtoupper, mb_.+</promote>',
        );
    }

    #[DataProvider('getClassLikeWithFunction')]
    public function testShouldFailNotDependsOnFunction(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependsOnFunction(
                        names: 'strtolower',
                        patterns: 'array_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not depends on functions %s but depends on %s',
                $exceptName,
                'strtolower, array_.+',
                'array_merge, strtolower',
            ),
        );
    }

    public static function getClassLikeWithFunction(): Generator
    {
        yield 'anonymous class' => [
            '<?php
             new class {
                public function __invoke() {
                    array_merge([], []);
                    strtolower("FOO");
                }
            };',
            'Anonymous',
        ];
    }
}
