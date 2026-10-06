<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveOnlyPrivateProperties;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveOnlyPrivateProperties::class)]
#[CoversMethod(MethodAssert::class, 'toHaveOnlyPrivateProperties')]
final class ToHaveOnlyPrivatePropertiesTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithPrivateProperties')]
    public function testToHaveOnlyPrivateProperties(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPrivateProperties(),
            );

        self::assertRulesPass($rules, 'to have only private properties');
    }

    public static function getClassLikeWithPrivateProperties(): Generator
    {
        yield 'no property' => ['<?php class Foo {}'];

        yield 'declared private properties' => [
            '<?php class Foo { private int $a = 0; private static ?string $b = null; '
            . 'private $c, $d; }',
        ];

        yield 'promoted private properties' => [
            '<?php final readonly class Foo { '
            . 'public function __construct(private int $a, private readonly string $b) {} }',
        ];

        yield 'constructor parameter not promoted' => [
            '<?php class Foo { public function __construct(int $a) {} }',
        ];

        yield 'public parameter of another method' => [
            '<?php class Foo { public function bar(int $a) {} }',
        ];

        yield 'trait' => ['<?php trait Foo { private int $a = 0; }'];

        yield 'anonymous class' => ['<?php return new class { private int $a = 0; };'];
    }

    #[DataProvider('getClassLikeWithNonPrivateProperty')]
    public function testShouldFailToHaveOnlyPrivateProperties(
        string $raw,
        string $visibility,
        string $exceptName = 'Foo',
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPrivateProperties(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have only private properties but <fire>$bar</fire> is %s',
                $exceptName,
                $visibility,
            ),
            2,
        );
    }

    public static function getClassLikeWithNonPrivateProperty(): Generator
    {
        yield 'declared public' => [
            <<<'PHP'
                <?php class Foo {
                    public int $bar = 0;
                }
                PHP,
            'public',
        ];

        yield 'declared protected' => [
            <<<'PHP'
                <?php class Foo {
                    protected int $bar = 0;
                }
                PHP,
            'protected',
        ];

        yield 'declared public static' => [
            <<<'PHP'
                <?php class Foo {
                    public static int $bar = 0;
                }
                PHP,
            'public',
        ];

        yield 'declared with var' => [
            <<<'PHP'
                <?php class Foo {
                    var $bar;
                }
                PHP,
            'public',
        ];

        yield 'promoted public' => [
            <<<'PHP'
                <?php class Foo {
                    public function __construct(public int $bar) {}
                }
                PHP,
            'public',
            'Foo',
        ];

        yield 'promoted protected' => [
            <<<'PHP'
                <?php class Foo {
                    public function __construct(protected int $bar) {}
                }
                PHP,
            'protected',
        ];

        yield 'promoted readonly without visibility' => [
            <<<'PHP'
                <?php class Foo {
                    public function __construct(readonly int $bar) {}
                }
                PHP,
            'public',
        ];

        yield 'trait' => [
            <<<'PHP'
                <?php trait Foo {
                    public int $bar = 0;
                }
                PHP,
            'public',
        ];

        yield 'anonymous class' => [
            <<<'PHP'
                <?php return new class {
                    public int $bar = 0;
                };
                PHP,
            'public', 'Anonymous',
        ];
    }

    public function testShouldFailToHaveOnlyPrivatePropertiesWithMultipleViolations(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                    <?php class Foo {
                        public $a, $b;
                        private int $c = 0;
                        public function __construct(
                            protected int $d,
                            private int $e,
                        ) {}
                    }
                    PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPrivateProperties(),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must have only private properties but <fire>$a</fire> is public',
                'Resource <promote>Foo</promote> must have only private properties but <fire>$b</fire> is public',
                'Resource <promote>Foo</promote> must have only private properties but <fire>$d</fire> is protected',
            ],
            [2, 2, 5],
        );
    }

    public function testShouldFailToHaveOnlyPrivatePropertiesAfterCompliantMembers(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                    <?php class Foo {
                        private int $c = 0;
                        public int $a = 0;
                        public function bar() {}
                        public function __construct(private int $e, public int $d) {}
                    }
                    PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert->toHaveOnlyPrivateProperties(),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must have only private properties but <fire>$a</fire> is public',
                'Resource <promote>Foo</promote> must have only private properties but <fire>$d</fire> is public',
            ],
            [3, 5],
        );
    }
}
