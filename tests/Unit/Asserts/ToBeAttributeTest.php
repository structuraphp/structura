<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Attribute;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeAttribute;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeAttribute::class)]
#[CoversMethod(TypeAssert::class, 'toBeAttribute')]
class ToBeAttributeTest extends TestCase
{
    use ArchitectureAsserts;

    /**
     * @param int-mask-of<Attribute::IS_REPEATABLE|Attribute::TARGET_*> $flag
     */
    #[DataProvider('getClassLikeForPass')]
    public function testToBeAttribute(string $raw, int $flag): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeAttribute($flag),
            );

        self::assertRulesPass($rules, 'to be attribute');
    }

    public static function getClassLikeForPass(): Generator
    {
        yield 'default parameter' => [
            '<?php #[Attribute()] class Foo {};',
            Attribute::TARGET_ALL,
        ];

        yield 'target class' => [
            '<?php #[Attribute(Attribute::TARGET_CLASS)] class Foo {};',
            Attribute::TARGET_CLASS,
        ];

        yield 'target function' => [
            '<?php #[Attribute(Attribute::TARGET_FUNCTION)] class Foo {};',
            Attribute::TARGET_FUNCTION,
        ];

        yield 'target method' => [
            '<?php #[Attribute(Attribute::TARGET_METHOD)] class Foo {};',
            Attribute::TARGET_METHOD,
        ];

        yield 'target property' => [
            '<?php #[Attribute(Attribute::TARGET_PROPERTY)] class Foo {};',
            Attribute::TARGET_PROPERTY,
        ];

        yield 'target class constant' => [
            '<?php #[Attribute(Attribute::TARGET_CLASS_CONSTANT)] class Foo {};',
            Attribute::TARGET_CLASS_CONSTANT,
        ];

        yield 'target parameter' => [
            '<?php #[Attribute(Attribute::TARGET_PARAMETER)] class Foo {};',
            Attribute::TARGET_PARAMETER,
        ];

        yield 'target all' => [
            '<?php #[Attribute(Attribute::TARGET_ALL)] class Foo {};',
            Attribute::TARGET_ALL,
        ];

        yield 'with a multiple mask' => [
            '<?php #[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_PARAMETER)] class Foo {};',
            Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_PARAMETER,
        ];

        yield 'repeatable' => [
            '<?php #[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PARAMETER)] class Foo {};',
            Attribute::IS_REPEATABLE | Attribute::TARGET_PARAMETER,
        ];
    }

    #[DataProvider('getClasseLikeForFail')]
    public function testShouldFailToBeAttribute(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeAttribute(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf('Resource <promote>%s</promote> must be attributable', $exceptName),
        );
    }

    public static function getClasseLikeForFail(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};', 'Anonymous'];

        yield 'class' => [
            '<?php class Foo {}',
        ];

        yield 'class with a bad parameter' => [
            '<?php #[Attribute(1)] class Foo {};',
        ];

        yield 'class with a bad parameter and multiple attributes' => [
            '<?php #[AllowDynamicProperties] #[Attribute(1)]  class Foo {};',
        ];

        yield 'abstract class' => [
            '<?php abstract class Foo {}',
        ];

        yield 'abstract class with attribute' => [
            '<?php #[Attribute()] abstract class Foo {}',
        ];

        yield 'enum' => [
            '<?php enum Foo {}',
        ];

        yield 'enum with attribute' => [
            '<?php #[Attribute()] enum Foo {}',
        ];

        yield 'trait' => [
            '<?php trait Foo {}',
        ];

        yield 'trait with attribute' => [
            '<?php #[Attribute()] trait Foo {}',
        ];

        yield 'interface' => [
            '<?php interface Foo {}',
        ];

        yield 'interface with attribute' => [
            '<?php #[Attribute()] interface Foo {}',
        ];
    }
}
