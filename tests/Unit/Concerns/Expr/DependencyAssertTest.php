<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Concerns\Expr;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use StructuraPhp\Structura\Concerns\Expr\DependencyAssert;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;

#[CoversMethod(DependencyAssert::class, 'toOnlyDependOnAttribute')]
#[CoversMethod(DependencyAssert::class, 'toOnlyDependOnImplementation')]
#[CoversMethod(DependencyAssert::class, 'toOnlyDependOnInheritance')]
#[CoversMethod(DependencyAssert::class, 'toOnlyDependOnUseTrait')]
#[CoversMethod(RelationAssert::class, 'toHaveAttribute')]
#[CoversMethod(RelationAssert::class, 'toHaveOnlyAttribute')]
#[CoversMethod(RelationAssert::class, 'toOnlyImplement')]
#[CoversMethod(RelationAssert::class, 'toExtend')]
#[CoversMethod(RelationAssert::class, 'toOnlyUseTrait')]
final class DependencyAssertTest extends TestCase
{
    /**
     * @param Closure(Expr): Expr $closure
     */
    #[DataProvider('getRelationProvider')]
    public function testRelationIsAuthorisedOnce(Closure $closure, string $relation): void
    {
        self::assertStringEndsWith(
            \sprintf('to only depend on %s <promote>Stringable</promote>', $relation),
            (string) $closure(new Expr()),
        );
    }

    /**
     * @return Generator<string, array{Closure(Expr): Expr, string}>
     */
    public static function getRelationProvider(): Generator
    {
        yield 'attribute from relation' => [
            static fn (Expr $expr): Expr => $expr
                ->toHaveAttribute(Stringable::class)
                ->toOnlyDependOnAttribute(),
            'attribute',
        ];

        yield 'attribute declared twice' => [
            static fn (Expr $expr): Expr => $expr
                ->toHaveAttribute(Stringable::class)
                ->toOnlyDependOnAttribute(Stringable::class),
            'attribute',
        ];

        yield 'only attribute from relation' => [
            static fn (Expr $expr): Expr => $expr
                ->toHaveOnlyAttribute(Stringable::class)
                ->toOnlyDependOnAttribute(),
            'attribute',
        ];

        yield 'implementation from relation' => [
            static fn (Expr $expr): Expr => $expr
                ->toOnlyImplement(Stringable::class)
                ->toOnlyDependOnImplementation(),
            'implementation',
        ];

        yield 'implementation declared twice' => [
            static fn (Expr $expr): Expr => $expr
                ->toOnlyImplement(Stringable::class)
                ->toOnlyDependOnImplementation(Stringable::class),
            'implementation',
        ];

        yield 'inheritance from relation' => [
            static fn (Expr $expr): Expr => $expr
                ->toExtend(Stringable::class)
                ->toOnlyDependOnInheritance(),
            'inheritance',
        ];

        yield 'inheritance declared twice' => [
            static fn (Expr $expr): Expr => $expr
                ->toExtend(Stringable::class)
                ->toOnlyDependOnInheritance(Stringable::class),
            'inheritance',
        ];

        yield 'trait from relation' => [
            static fn (Expr $expr): Expr => $expr
                ->toOnlyUseTrait(Stringable::class)
                ->toOnlyDependOnUseTrait(),
            'trait',
        ];

        yield 'trait declared twice' => [
            static fn (Expr $expr): Expr => $expr
                ->toOnlyUseTrait(Stringable::class)
                ->toOnlyDependOnUseTrait(Stringable::class),
            'trait',
        ];
    }
}
