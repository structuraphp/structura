<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToOnlyDependOnInheritance;
use StructuraPhp\Structura\Concerns\Expr\DependencyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToOnlyDependOnInheritance::class)]
#[CoversMethod(DependencyAssert::class, 'toOnlyDependOnInheritance')]
final class ToOnlyDependOnInheritanceTest extends TestCase
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
                    ->toOnlyDependOnInheritance(
                        names: ControllerBase::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to only depend on inheritance <promote>%s, %s</promote>',
                ControllerBase::class,
                'Dependencies\Acme\.*',
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

    #[DataProvider('getClassLikeWithoutInheritance')]
    public function testShouldFailToExtendsWithInterface(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyDependOnInheritance(
                        names: ControllerBase::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must only extend classes from these namespaces %s, %s but extends <fire>%s</fire>',
                ControllerBase::class,
                'Dependencies\Acme\.*',
                'BadExtends',
            ),
        );
    }

    public static function getClassLikeWithoutInheritance(): Generator
    {
        yield 'with bad extends' => ['<?php class Foo extends \BadExtends {}'];

        yield 'with bad extends and good pattern' => ['<?php interface Foo extends \BadExtends, \Dependencies\Acme\Foo {}'];
    }

    public function testShouldFailToExtendsWithInterfaceMultiple(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php interface Foo extends \BadExtends2, \BadExtends1, \StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toOnlyDependOnInheritance(
                        names: ControllerBase::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>Foo</promote> must only extend classes from these namespaces %s, %s but extends <fire>%s</fire>',
                    ControllerBase::class,
                    'Dependencies\Acme\.*',
                    'BadExtends2',
                ),
                \sprintf(
                    'Resource <promote>Foo</promote> must only extend classes from these namespaces %s, %s but extends <fire>%s</fire>',
                    ControllerBase::class,
                    'Dependencies\Acme\.*',
                    'BadExtends1',
                ),
            ],
        );
    }
}
