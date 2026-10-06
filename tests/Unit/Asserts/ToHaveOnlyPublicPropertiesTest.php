<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveOnlyPublicProperties;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveOnlyPublicProperties::class)]
#[CoversMethod(MethodAssert::class, 'toHaveOnlyPublicProperties')]
final class ToHaveOnlyPublicPropertiesTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithPublicProperties')]
    public function testToHaveOnlyPublicProperties(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPublicProperties(),
            );

        self::assertRulesPass($rules, 'to have only public properties');
    }

    public static function getClassLikeWithPublicProperties(): Generator
    {
        yield 'no property' => ['<?php class Foo {}'];

        yield 'declared public properties' => [
            '<?php class Foo { public int $a = 0; public static ?string $b = null; '
            . 'var $c; public $d, $e; }',
        ];

        yield 'promoted public properties' => [
            '<?php final readonly class Foo { '
            . 'public function __construct(public int $a, public readonly string $b) {} }',
        ];

        yield 'promoted readonly without visibility' => [
            '<?php class Foo { public function __construct(readonly int $a) {} }',
        ];

        yield 'constructor parameter not promoted' => [
            '<?php class Foo { public function __construct(int $a) {} }',
        ];

        yield 'trait' => ['<?php trait Foo { public int $a = 0; }'];

        yield 'anonymous class' => ['<?php return new class { public int $a = 0; };'];
    }

    #[DataProvider('getClassLikeWithNonPublicProperty')]
    public function testShouldFailToHaveOnlyPublicProperties(
        string $raw,
        string $visibility,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPublicProperties(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have only public properties but <fire>$bar</fire> is %s',
                $exceptName,
                $visibility,
            ),
            2,
        );
    }

    public static function getClassLikeWithNonPublicProperty(): Generator
    {
        yield 'declared private' => [
            <<<'PHP'
                <?php class Foo {
                    private int $bar = 0;
                }
                PHP,
            'private',
        ];

        yield 'declared protected' => [
            <<<'PHP'
                <?php class Foo {
                    protected int $bar = 0;
                }
                PHP,
            'protected',
        ];

        yield 'declared private static' => [
            <<<'PHP'
                <?php class Foo {
                    private static int $bar = 0;
                }
                PHP,
            'private',
        ];

        yield 'promoted private' => [
            <<<'PHP'
                <?php class Foo {
                    public function __construct(private int $bar) {}
                }
                PHP,
            'private',
        ];

        yield 'promoted protected' => [
            <<<'PHP'
                <?php class Foo {
                    public function __construct(protected int $bar) {}
                }
                PHP,
            'protected',
        ];

        yield 'trait' => [
            <<<'PHP'
                <?php trait Foo {
                    private int $bar = 0;
                }
                PHP,
            'private',
        ];

        yield 'anonymous class' => [
            <<<'PHP'
                <?php return new class {
                    private int $bar = 0;
                };
                PHP,
            'private', 'Anonymous',
        ];
    }

    public function testShouldFailToHaveOnlyPublicPropertiesAfterCompliantMembers(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                    <?php class Foo {
                        public int $c = 0;
                        private int $a = 0;
                        public function bar() {}
                        public function __construct(public int $e, protected int $d) {}
                    }
                    PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPublicProperties(),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must have only public properties but <fire>$a</fire> is private',
                'Resource <promote>Foo</promote> must have only public properties but <fire>$d</fire> is protected',
            ],
            [3, 5],
        );
    }
}
