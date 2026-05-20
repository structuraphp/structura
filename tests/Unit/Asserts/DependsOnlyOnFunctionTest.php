<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\DependsOnlyOnFunction;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOnFunction::class)]
#[CoversMethod(DependencyAssert::class, 'dependsOnlyOnFunction')]
class DependsOnlyOnFunctionTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithFunction')]
    public function testDependsOnlyOnFunctionWithClass(string $raw, string $exceptName): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnFunction(
                        names: ['strtolower', 'mb_strlen'],
                        patterns: ['array_.+', 'date_.+'],
                    ),
            );

        self::assertRulesPass(
            $rules,
            'depends only on function <promote>strtolower, mb_strlen, array_.+, [1+]</promote>',
        );
    }

    #[DataProvider('getScriptWithFunction')]
    public function testDependsOnlyOnFunctionWithScript(string $raw, string $exceptName): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->dependsOnlyOnFunction(
                        names: ['strtolower', 'mb_strlen'],
                        patterns: ['array_.+', 'date_.+'],
                    ),
            );

        self::assertRulesPass(
            $rules,
            'depends only on function <promote>strtolower, mb_strlen, array_.+, [1+]</promote>',
        );
    }

    #[DataProvider('getClassLikeWithFunction')]
    public function testShouldFailDependsOnlyOnFunction(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnFunction(
                        names: ['strtoupper', 'date_create'],
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>%s</promote> must depends only on functions %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtoupper, date_create, mb_.+',
                    'array_merge',
                ),
                sprintf(
                    'Resource <promote>%s</promote> must depends only on functions %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtoupper, date_create, mb_.+',
                    'strtolower',
                ),
            ],
            [2, 2],
        );
    }

    public static function getClassLikeWithFunction(): Generator
    {
        yield 'anonymous class' => [
            '<?php
             return new class {
                public function __invoke() {
                    mb_strlen("FOO");
                    strtolower("FOO");
                    array_merge([], []);
                    date_create("now");
                }
            };',
            'Anonymous',
        ];
    }

    #[DataProvider('getScriptWithFunction')]
    public function testShouldFailDependsOnlyOnFunctionWithScript(
        string $raw,
        string $exceptName,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->dependsOnlyOnFunction(
                        names: ['strtoupper', 'date_create'],
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            [
                \sprintf(
                    'Resource <promote>%s</promote> must depends only on functions %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtoupper, date_create, mb_.+',
                    'array_merge',
                ),
                \sprintf(
                    'Resource <promote>%s</promote> must depends only on functions %s but depends on <fire>%s</fire>',
                    $exceptName,
                    'strtoupper, date_create, mb_.+',
                    'strtolower',
                ),
            ],
            0,
        );
    }

    public static function getScriptWithFunction(): Generator
    {
        yield 'script with namespace' => [
            <<<'PHP'
            <?php

            namespace Foo;

            function bar() {
                mb_strlen("BAR");
                strtolower("FOO");
                array_merge([], []);
                date_create("now");
            }
            PHP,
            'Foo',
        ];

        yield 'script without namespace' => [
            <<<'PHP'
            <?php

            function bar() {
                mb_strlen("BAR");
                strtolower("FOO");
                array_merge([], []);
                date_create("now");
            }
            PHP,
            'tmp/run_0.php',
        ];
    }
}
