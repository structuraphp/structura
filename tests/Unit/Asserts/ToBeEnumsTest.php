<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeEnums;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeEnums::class)]
#[CoversMethod(Expr::class, 'toBeEnums')]
final class ToBeEnumsTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeEnums(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php enum Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeEnums(),
            );

        self::assertRulesPass($rules, 'to be enums');
    }

    #[DataProvider('getClassLikeNonEnums')]
    public function testShouldFailToBeEnums(
        string $raw,
        ClassType $classType,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeEnums(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must be an enum but is <fire>%s</fire>',
                $exceptName,
                $classType->label(),
            ),
        );
    }

    public static function getClassLikeNonEnums(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', ClassType::AnonymousClass_, 'Anonymous'];

        yield 'class' => ['<?php class Foo {}', ClassType::Class_];

        yield 'interface' => ['<?php interface Foo {}', ClassType::Interface_];

        yield 'trait' => ['<?php trait Foo {}', ClassType::Trait_];
    }
}
