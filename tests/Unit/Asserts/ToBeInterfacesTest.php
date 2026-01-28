<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeInterfaces;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeInterfaces::class)]
#[CoversMethod(Expr::class, 'toBeInterfaces')]
final class ToBeInterfacesTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeInterface(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php interface Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
            );

        self::assertRulesPass($rules, 'to be interfaces');
    }

    #[DataProvider('getClassLikeNonEnums')]
    public function testShouldFailToBeInterface(
        string $raw,
        ClassType $classType,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must be an interface but is <fire>%s</fire>',
                $exceptName,
                $classType->label(),
            ),
        );
    }

    public static function getClassLikeNonEnums(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', ClassType::AnonymousClass_, 'Anonymous'];

        yield 'class' => ['<?php class Foo {}', ClassType::Class_];

        yield 'enum' => ['<?php enum Foo {}', ClassType::Enum_];

        yield 'trait' => ['<?php trait Foo {}', ClassType::Trait_];
    }
}
