<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Fixture\Concerns\HasFactory;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToOnlyUseTest::class)]
#[CoversMethod(Expr::class, 'toOnlyUse')]
final class ToOnlyUseTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithTrait')]
    public function testToOnlyUse(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyUse(HasFactory::class),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testShouldFailToOnlyUse(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> should only use trait <promote>%s</promote>',
                $exceptName,
                HasFactory::class,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyUse(HasFactory::class),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class { use \Structura\Tests\Fixture\Concerns\HasFactory; };'];

        yield 'class' => ['<?php class Foo { use \Structura\Tests\Fixture\Concerns\HasFactory; }'];

        yield 'enum' => ['<?php enum Foo { use \Structura\Tests\Fixture\Concerns\HasFactory; };'];

        yield 'interface' => ['<?php interface Foo { use \Structura\Tests\Fixture\Concerns\HasFactory; }'];
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];
    }
}
