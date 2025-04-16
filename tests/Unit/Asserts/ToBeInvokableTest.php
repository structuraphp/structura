<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Asserts\ToHaveMethod;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveMethod::class)]
#[CoversMethod(Expr::class, 'toBeInvokable')]
final class ToBeInvokableTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeInvokable(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo { public function __invoke() {} }')
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeInvokable(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonInvokable')]
    public function testShouldFailToBeInvokable(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must have method <promote>__invoke</promote>',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toBeInvokable(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeNonInvokable(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'enum' => ['<?php enum Foo {}'];
        yield 'trait' => ['<?php trait Foo {}'];
    }
}
