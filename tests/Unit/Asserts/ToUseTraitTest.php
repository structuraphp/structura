<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToUseTrait;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToUseTrait::class)]
#[CoversMethod(Expr::class, 'toUseTrait')]
final class ToUseTraitTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithTrait')]
    public function testToExtend(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseTrait(HasFactory::class),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to use trait <promote>%s</promote>',
                HasFactory::class,
            ),
        );
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => [
            '<?php new class { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; };',
        ];

        yield 'class' => [
            '<?php class Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; }',
        ];

        yield 'enum' => [
            '<?php enum Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; };',
        ];

        yield 'interface' => [
            '<?php interface Foo { use \StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory; }',
        ];
    }

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testShouldFailToExtendsWithInterface(
        string $raw,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseTrait(HasFactory::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must use traits <promote>%s</promote>',
                $exceptName,
                HasFactory::class,
            ),
        );
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];
    }
}
