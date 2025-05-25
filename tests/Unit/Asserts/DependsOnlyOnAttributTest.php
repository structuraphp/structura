<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;
use StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOnAttribut::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOnAttribut')]
final class DependsOnlyOnAttributTest extends TestCase
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
                    ->dependsOnlyOnAttribut(
                        names: SensitiveParameter::class,
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
                    ->dependsOnlyOnAttribut(
                        names: SensitiveParameter::class,
                        patterns: 'Dependencies\Acme\.*',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Foo</promote> must use attributes on these namespaces %s, %s but use attributes %s',
                SensitiveParameter::class,
                'Dependencies\Acme\.*',
                'BadAttribute',
            ),
        );
    }

    public static function getClassLikeWithInheritance(): Generator
    {
        yield 'without attributs' => ['<?php class Foo {}'];

        yield 'without attributs and another dependency' => ['<?php use \ArrayAccess; class Foo {}'];

        yield 'with name' => ['<?php #[\SensitiveParameter] class Foo {}'];

        yield 'with pattern' => ['<?php #[\Dependencies\Acme\Foo] class Foo {}'];

        yield 'with name and pattern' => [
            '<?php #[\Dependencies\Acme\Foo] #[\SensitiveParameter] class Foo {}',
        ];
    }

    public static function getClassLikeWithoutInheritance(): Generator
    {
        yield 'with bad attribut' => ['<?php #[\BadAttribute] class Foo {}'];

        yield 'with bad attribut and good pattern' => ['<?php #[\BadAttribute] #[\Dependencies\Acme\Foo] class Foo {}'];

        yield 'with bad name and good name' => [
            '<?php #[\BadAttribute] #[\SensitiveParameter] class Foo {}',
        ];
    }
}
