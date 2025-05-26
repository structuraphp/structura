<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOnUseTrait::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOnUseTrait')]
final class DependsOnlyOnUseTraitTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithTrait')]
    public function testToExtend(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnUseTrait(
                        names: HasFactory::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesPass($rules);
    }

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testShouldFailToExtendsWithInterface(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnUseTrait(
                        names: HasFactory::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must use traits on these namespaces %s, %s but uses these traits BadTrait',
                HasFactory::class,
                'Dependencies\Acme\.*',
            ),
        );
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'without trait' => ['<?php class Foo {}'];

        yield 'without trait and another dependency' => ['<?php use \ArrayAccess; class Foo {}'];

        yield 'with name' => ['<?php class Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; }'];

        yield 'with pattern' => ['<?php class Foo { use \Dependencies\Acme\Foo; }'];

        yield 'with name and pattern' => ['<?php class Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory, \Dependencies\Acme\Foo; }'];
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'with bad trait' => ['<?php class Foo { use \BadTrait; }'];

        yield 'with bad trait and good pattern' => ['<?php class Foo { use \BadTrait, \Dependencies\Acme\Foo; }'];

        yield 'with bad name and good name' => ['<?php class Foo { use \BadTrait, \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; }'];
    }
}
