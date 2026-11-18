<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotDependOnFunction;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotDependOnFunction::class)]
#[CoversMethod(DependencyAssert::class, 'toNotDependOnFunction')]
class ToNotDependOnFunctionTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithFunction')]
    public function testNotDependOnFunctionWithClass(string $raw, string $exceptName): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOnFunction(
                        names: 'strtoupper',
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to not depend on function <promote>strtoupper, mb_.+</promote>',
        );
    }

    /**
     * @param array<int, int> $lines
     */
    #[DataProvider('getScriptWithFunction')]
    public function testNotDependOnFunctionWithScript(string $raw, string $exceptName, array $lines): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotDependOnFunction(
                        names: 'strtoupper',
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to not depend on function <promote>strtoupper, mb_.+</promote>',
        );
    }

    #[DataProvider('getClassLikeWithFunction')]
    public function testShouldFailNotDependOnFunction(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOnFunction(
                        names: 'strtolower',
                        patterns: 'array_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>%s</promote> must not depend on functions <promote>%s</promote> but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtolower, array_.+',
                    'array_merge',
                ),
                sprintf(
                    'Resource <promote>%s</promote> must not depend on functions <promote>%s</promote> but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtolower, array_.+',
                    'strtolower',
                ),
            ],
            [4, 5],
        );
    }

    public static function getClassLikeWithFunction(): Generator
    {
        yield 'anonymous class' => [
            '<?php
             return new class {
                public function __invoke() {
                    array_merge([], []);
                    strtolower("FOO");
                }
            };',
            'Anonymous',
        ];
    }

    /**
     * @param array<int, int> $lines
     */
    #[DataProvider('getScriptWithFunction')]
    public function testShouldFailNotDependOnFunctionWithScript(
        string $raw,
        string $exceptName,
        array $lines,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotDependOnFunction(
                        names: 'strtolower',
                        patterns: 'array_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>%s</promote> must not depend on functions <promote>%s</promote> but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtolower, array_.+',
                    'array_merge',
                ),
                sprintf(
                    'Resource <promote>%s</promote> must not depend on functions <promote>%s</promote> but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtolower, array_.+',
                    'strtolower',
                ),
            ],
            $lines,
        );
    }

    public static function getScriptWithFunction(): Generator
    {
        yield 'script with namespace' => [
            <<<'PHP'
            <?php

            namespace Foo;

            function bar() {
                array_merge([], []);
                strtolower("FOO");
            }
            PHP,
            'Foo',
            [6, 7],
        ];

        yield 'script without namespace' => [
            <<<'PHP'
            <?php

            function bar() {
                array_merge([], []);
                strtolower("FOO");
            }
            PHP,
            'tmp/run_0.php',
            [4, 5],
        ];
    }

    public function testDuplicateFunctionCallsProduceSingleViolation(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                return new class {
                    public function __invoke() {
                        strtolower("foo");
                        strtolower("bar");
                    }
                };
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOnFunction(names: ['strtolower']),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Anonymous</promote> must not depend on functions <promote>%s</promote> but depends on <fire>%s</fire>',
                'strtolower',
                'strtolower',
            ),
            4,
        );
    }

    public function testOnlyIntersectionProducesViolations(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(
                <<<'PHP'
                <?php
                return new class {
                    public function __invoke() {
                        strtolower("foo");
                    }
                };
                PHP,
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotDependOnFunction(names: ['strtolower', 'strtoupper']),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>Anonymous</promote> must not depend on functions <promote>%s, %s</promote> but depends on <fire>%s</fire>',
                'strtolower',
                'strtoupper',
                'strtolower',
            ),
            4,
        );
    }

    public function testShouldFailToNotDependOnFunctionPatternOnlySorted(): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw('<?php \Depend\zeta(); \Depend\alpha();')
            ->should(static fn (ExprScript $assert): ExprScript => $assert->toNotDependOnFunction(patterns: 'Depend\.+'));

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>tmp/run_0.php</promote> must not depend on functions <promote>Depend\.+</promote> but depends on <fire>Depend\alpha</fire>',
                'Resource <promote>tmp/run_0.php</promote> must not depend on functions <promote>Depend\.+</promote> but depends on <fire>Depend\zeta</fire>',
            ],
            [1, 1],
        );
    }
}
