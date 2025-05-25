<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOnImplementation::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOnImplementation')]
final class DependsOnlyOnImplementationTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithInheritance')]
    public function testToExtend(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnImplementation(
                        names: ArrayAccess::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesPass($rules);
    }

    #[DataProvider('getClassLikeWithoutInheritance')]
    public function testShouldFailToExtendsWithInterface(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnImplementation(
                        names: ArrayAccess::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must inherit on these namespaces %s, %s but implement %s',
                ArrayAccess::class,
                'Dependencies\Acme\.*',
                'BadImplements',
            ),
        );
    }

    public static function getClassLikeWithInheritance(): Generator
    {
        yield 'without implements' => ['<?php class Foo {}'];

        yield 'without implements and another dependency' => ['<?php use \ArrayAccess; class Foo {}'];

        yield 'with name' => ['<?php class Foo implements \ArrayAccess {}'];

        yield 'with pattern' => ['<?php class Foo implements \Dependencies\Acme\Foo {}'];

        yield 'with name and pattern' => [
            '<?php class Foo implements \ArrayAccess, \Dependencies\Acme\Foo {}',
        ];
    }

    public static function getClassLikeWithoutInheritance(): Generator
    {
        yield 'with bad implements' => ['<?php class Foo implements \BadImplements {}'];

        yield 'with bad implements and good pattern' => ['<?php class Foo implements \BadImplements, \Dependencies\Acme\Foo {}'];

        yield 'with bad name and good name' => [
            '<?php class Foo implements \BadImplements, \ArrayAccess {}',
        ];
    }
}
