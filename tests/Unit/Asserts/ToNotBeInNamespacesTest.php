<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotBeInNamespaces;
use StructuraPhp\Structura\Concerns\Expr\ThridPartyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotBeInNamespaces::class)]
#[CoversMethod(ThridPartyAssert::class, 'toBeInNamespaces')]
class ToNotBeInNamespacesTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeForPass')]
    public function testToBeInNamespaces(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotBeInNamespaces('Foo\.*'),
            );

        self::assertRulesPass(
            $rules,
            'to not be in one of the namespaces <promote>Foo\.*</promote>',
        );
    }

    public static function getClassLikeForPass(): Generator
    {
        // Anonymous classes cannot have namespaces
        yield 'anonymous class' => ['<?php namespace Acme\Bar; new class {};'];

        yield 'class' => ['<?php namespace Acme\Bar; class Foo {}'];

        yield 'enum' => ['<?php namespace Acme\Bar; enum Foo {}'];

        yield 'interface' => ['<?php namespace Acme\Bar; interface Foo {}'];

        yield 'trait' => ['<?php namespace Acme\Bar; trait Foo {}'];
    }

    #[DataProvider('getClasseLikeForFail')]
    public function testShouldFailToBeInNamespaces(
        string $raw,
        string $exceptName = 'Acme\Bar\Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotBeInNamespaces('Acme\.*'),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not be in one of the namespaces <promote>%s</promote>',
                $exceptName,
                'Acme\.*',
            ),
        );
    }

    public static function getClasseLikeForFail(): Generator
    {
        yield 'class' => ['<?php namespace Acme\Bar; class Foo {}'];

        yield 'enum' => ['<?php namespace Acme\Bar; enum Foo {}'];

        yield 'interface' => ['<?php namespace Acme\Bar; interface Foo {}'];

        yield 'trait' => ['<?php namespace Acme\Bar; trait Foo {}'];
    }
}
