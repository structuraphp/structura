<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOnInheritance::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOnInheritance')]
final class DependsOnlyOnInheritanceTest extends TestCase
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
                    ->dependsOnlyOnInheritance(
                        names: ControllerBase::class,
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
                    ->dependsOnlyOnInheritance(
                        names: ControllerBase::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must inherit on these namespaces %s, %s but inherits %s',
                ControllerBase::class,
                'Dependencies\Acme\.*',
                'BadExtends',
            ),
        );
    }

    public static function getClassLikeWithInheritance(): Generator
    {
        yield 'without extends' => ['<?php class Foo {}'];

        yield 'without extends and another dependency' => ['<?php use \ArrayAccess; class Foo {}'];

        yield 'with name' => ['<?php class Foo extends \StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase {}'];

        yield 'with pattern' => ['<?php class Foo extends \Dependencies\Acme\Foo {}'];

        yield 'with name and pattern' => [
            '<?php interface Foo extends \Dependencies\Acme\Foo, \StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase {}',
        ];
    }

    public static function getClassLikeWithoutInheritance(): Generator
    {
        yield 'with bad extends' => ['<?php class Foo extends \BadExtends {}'];

        yield 'with bad extends and good pattern' => ['<?php interface Foo extends \BadExtends, \Dependencies\Acme\Foo {}'];

        yield 'with bad name and good name' => [
            '<?php interface Foo extends \BadExtends, \StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase {}',
        ];
    }
}
