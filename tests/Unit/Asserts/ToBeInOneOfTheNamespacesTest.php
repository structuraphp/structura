<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeInOneOfTheNamespaces::class)]
#[CoversMethod(Expr::class, 'toBeInOneOfTheNamespaces')]
class ToBeInOneOfTheNamespacesTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeForPass')]
    public function testToBeInOneOfTheNamespaces(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeInOneOfTheNamespaces('Acme\.*'),
            );

        self::assertRulesPass(
            $rules,
            'to be in one of the namespaces <promote>Acme\.*</promote>',
        );
    }

    #[DataProvider('getClasseLikeForFail')]
    public function testShouldFailToBeInOneOfTheNamespaces(
        string $raw,
        string $exceptName = 'Acme\Bar\Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeInOneOfTheNamespaces('Acme\Foo'),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> to be in one of the namespaces <promote>%s</promote>',
                $exceptName,
                'Acme\Foo',
            ),
        );
    }

    public static function getClassLikeForPass(): Generator
    {
        yield 'class' => ['<?php namespace Acme\Bar; class Foo {}'];

        yield 'enum' => ['<?php namespace Acme\Bar; enum Foo {}'];

        yield 'interface' => ['<?php namespace Acme\Bar; interface Foo {}'];

        yield 'trait' => ['<?php namespace Acme\Bar; trait Foo {}'];
    }

    public static function getClasseLikeForFail(): Generator
    {
        // Anonymous classes cannot have namespaces
        yield 'anonymous class' => ['<?php namespace Acme\Foo; new class {};', 'Anonymous'];

        yield 'class' => ['<?php namespace Acme\Bar; class Foo {}'];

        yield 'enum' => ['<?php namespace Acme\Bar; enum Foo {}'];

        yield 'interface' => ['<?php namespace Acme\Bar; interface Foo {}'];

        yield 'trait' => ['<?php namespace Acme\Bar; trait Foo {}'];
    }
}
