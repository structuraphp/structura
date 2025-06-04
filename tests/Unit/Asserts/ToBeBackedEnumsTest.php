<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeBackedEnums;
use StructuraPhp\Structura\Enums\ScalarType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeBackedEnums::class)]
#[CoversMethod(Expr::class, 'toBeBackedEnums')]
class ToBeBackedEnumsTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeForPass')]
    public function testToBackedEnums(string $raw, ?ScalarType $scalarType): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeBackedEnums($scalarType),
            );

        self::assertRulesPass($rules);
    }

    #[DataProvider('getClasseLikeForFail')]
    public function testShouldFailToBackedEnums(
        string $raw,
        ?ScalarType $scalarType,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeBackedEnums($scalarType),
            );

        $messageViolation = \sprintf(
            'Resource <promote>%s</promote> must be an enums type of <promote>%s</promote>',
            $exceptName,
            $scalarType->value ?? 'int or string',
        );

        self::assertRulesViolation($rules, $messageViolation);
    }

    public static function getClassLikeForPass(): Generator
    {
        yield 'backed string' => [
            '<?php enum Foo: string {}',
            ScalarType::String,
        ];

        yield 'backed int' => [
            '<?php enum Foo: int {}',
            ScalarType::Int,
        ];

        yield 'backed string without type' => [
            '<?php enum Foo: string {}',
            null,
        ];

        yield 'backed int without type' => [
            '<?php enum Foo: int {}',
            null,
        ];
    }

    public static function getClasseLikeForFail(): Generator
    {
        yield 'anonymous class' => [
            '<?php new class {};',
            null,
            'Anonymous',
        ];

        yield 'class' => [
            '<?php class Foo {}',
            null,
        ];

        yield 'enum without string type' => [
            '<?php enum Foo {}',
            ScalarType::String,
        ];

        yield 'enum without int type' => [
            '<?php enum Foo {}',
            ScalarType::Int,
        ];

        yield 'trait' => [
            '<?php trait Foo {}',
            null,
        ];

        yield 'interface' => [
            '<?php interface Foo {}',
            null,
        ];
    }
}
