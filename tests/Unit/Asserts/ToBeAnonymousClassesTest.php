<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeAnonymousClasses;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeAnonymousClasses::class)]
#[CoversMethod(TypeAssert::class, 'toBeAnonymousClasses')]
final class ToBeAnonymousClassesTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeAnonymousClasses(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php return new class {};')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeAnonymousClasses(),
            );

        self::assertRulesPass($rules, 'to be anonymous classes');
    }

    #[DataProvider('getClassLikeNonAnonymousClasses')]
    public function testShouldFailToBeAnonymousClasses(
        string $raw,
        ClassType $classType,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeAnonymousClasses(),
            );

        self::assertRulesViolation(
            $rules,
            sprintf(
                'Resource <promote>%s</promote> must be an anonymous class but is <fire>%s</fire>',
                $exceptName,
                $classType->label(),
            ),
        );
    }

    public static function getClassLikeNonAnonymousClasses(): Generator
    {
        yield 'class' => ['<?php class Foo {}', ClassType::Class_];

        yield 'enum' => ['<?php enum Foo {};', ClassType::Enum_];

        yield 'interface' => ['<?php interface Foo {}', ClassType::Interface_];

        yield 'trait' => ['<?php trait Foo {}', ClassType::Trait_];

        yield 'anonymous class not returned' => ['<?php new class {};', ClassType::Class_, ''];
    }
}
