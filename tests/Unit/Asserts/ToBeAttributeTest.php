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
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeAttribute::class)]
#[CoversMethod(Expr::class, 'toBeAttribute')]
class ToBeAttributeTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeNonClasses')]
    public function testToBeClasses(string $raw, int $flag): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeAttribute($flag),
            );

        self::assertRulesPass($rules);
    }

    public static function getClassLikeNonClasses(): Generator
    {
        yield 'class' => [
            '<?php #[Attribute()] class Foo {};',
            Attribute::TARGET_ALL,
        ];

        yield 'class 2' => [
            '<?php #[Attribute(Attribute::TARGET_ALL)] class Foo {};',
            Attribute::TARGET_ALL,
        ];

        yield 'class 3' => [
            '<?php #[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_PARAMETER)] class Foo {};',
            Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT,
        ];
    }
}
