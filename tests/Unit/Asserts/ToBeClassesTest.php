<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeClasses::class)]
#[CoversMethod(Expr::class, 'toBeClasses')]
final class ToBeClassesTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeClasses(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeClasses(),
            );

        self::assertRulesPass($rules, 'to be classes');
    }

    #[DataProvider('getClassLikeNonClasses')]
    public function testShouldFailToBeClasses(
        string $raw,
        ClassType $classType,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeClasses(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must be a class but is <fire>%s</fire>',
                $exceptName,
                $classType->label(),
            ),
        );
    }

    public static function getClassLikeNonClasses(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', ClassType::AnonymousClass_, 'Anonymous'];

        yield 'enum' => ['<?php enum Foo {}', ClassType::Enum_];

        yield 'interface' => ['<?php interface Foo {}', ClassType::Interface_];

        yield 'trait' => ['<?php trait Foo {}', ClassType::Trait_];
    }
}
