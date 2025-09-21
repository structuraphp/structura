<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\DependsOnlyOnFunction;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(DependsOnlyOnFunction::class)]
#[CoversMethod(Expr::class, 'dependsOnlyOnFunction')]
class DependsOnlyOnFunctionTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithFunction')]
    public function testDependsOnlyOnFunctionWithClass(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->dependsOnlyOnFunction(
                        names: 'strtolower',
                        patterns: 'array_.+',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'depends only on function <promote>strtolower, array_.+</promote>',
        );
    }

    #[DataProvider('getScriptWithFunction')]
    public function testDependsOnlyOnFunctionWithScript(string $raw): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->dependsOnlyOnFunction(
                        names: 'strtolower',
                        patterns: 'array_.+',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'depends only on function <promote>strtolower, array_.+</promote>',
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
                        names: 'strtoupper',
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must depends only on functions %s but depends on %s',
                $exceptName,
                'strtoupper, mb_.+',
                'array_merge, strtolower',
            ),
        );
    }

    #[DataProvider('getScriptWithFunction')]
    public function testShouldFailDependsOnlyOnFunctionWithScript(string $raw): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->dependsOnlyOnFunction(
                        names: 'strtoupper',
                        patterns: 'mb_.+',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must depends only on functions %s but depends on %s',
                'Foo',
                'strtoupper, mb_.+',
                'array_merge, strtolower',
            ),
        );
    }

    public static function getClassLikeWithFunction(): Generator
    {
        yield 'anonymous class' => [
            '<?php
             new class {
                public function __invoke() {
                    array_merge([], []);
                    strtolower("FOO");
                }
            };',
            'Anonymous',
        ];
    }

    public static function getScriptWithFunction(): Generator
    {
        yield 'script' => [
            <<<'PHP'
            <?php

            namespace Foo;

            function bar() {
                array_merge([], []);
                strtolower("FOO");
            }
            PHP,
        ];
    }
}
