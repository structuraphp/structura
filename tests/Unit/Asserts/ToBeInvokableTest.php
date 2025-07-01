<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

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
                static fn (Expr $assert): Expr => $assert->toBeInvokable(),
            );

        self::assertRulesPass(
            $rules,
            'to have method <promote>__invoke</promote>',
        );
    }

    #[DataProvider('getClassLikeNonInvokable')]
    public function testShouldFailToBeInvokable(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeInvokable(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have method <promote>__invoke</promote>',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeNonInvokable(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];

        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}
