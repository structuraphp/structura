<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToUseInclude;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToUseInclude::class)]
#[CoversMethod(Expr::class, 'toUseInclude')]
final class ToUseIncludeTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithIncludesProvider')]
    public function testToUseRequireWithClass(
        string $rawClass,
        IncludeType $includeType,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseInclude($includeType),
            );

        self::assertRulesPass(
            $rules,
            sprintf('to use <promote>%s</promote>', $includeType->label()),
        );
    }

    public static function getClassLikeWithIncludesProvider(): Generator
    {
        $classLike = [
            'anonymous class' => '<?php new class { public function bar() { %s } };',
            'class' => '<?php class Foo { public function bar() { %s } }',
            'enum' => '<?php enum Foo { public function bar() { %s } }',
            'interface' => '<?php %s interface Foo {}',
        ];

        /** @var array<string,IncludeType> $includes */
        $includes = [
            'require "foo.php";' => IncludeType::Require,
            'require_once "foo.php";' => IncludeType::RequireOnce,
            'include "foo.php";' => IncludeType::Include,
            'include_once "foo.php";' => IncludeType::IncludeOnce,
        ];

        foreach ($classLike as $keyClass => $expected) {
            foreach ($includes as $code => $include) {
                yield sprintf('%s - with %s', $keyClass, $include->label()) => [
                    sprintf($expected, $code),
                    $include,
                ];
            }
        }
    }

    #[DataProvider('getScriptLikeWithIncludesProvider')]
    public function testToUseRequireWithScript(
        string $rawScript,
        IncludeType $includeType,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseInclude($includeType),
            );

        self::assertRulesPass(
            $rules,
            sprintf('to use <promote>%s</promote>', $includeType->label()),
        );
    }

    public static function getScriptLikeWithIncludesProvider(): Generator
    {
        yield 'with require' => ['<?php require "foo.php";', IncludeType::Require];

        yield 'with require_once' => ['<?php require_once "foo.php";', IncludeType::RequireOnce];

        yield 'with include' => ['<?php include "foo.php";', IncludeType::Include];

        yield 'with include_once' => ['<?php include_once "foo.php";', IncludeType::IncludeOnce];
    }

    #[DataProvider('getClassLikeWithoutIncludeProvider')]
    public function testShouldFailToUseIncludeWithClass(
        string $rawClass,
        string $name,
        IncludeType $includeType,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseInclude($includeType),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must use <fire>%s</fire>',
                $name,
                $includeType->label(),
            ),
        );
    }

    public static function getClassLikeWithoutIncludeProvider(): Generator
    {
        $classLike = [
            'anonymous class' => '<?php new class { public function bar() { %s } };',
            'class' => '<?php class Foo { public function bar() { %s } }',
            'enum' => '<?php enum Foo { public function bar() { %s } }',
            'interface' => '<?php %s interface Foo {}',
        ];

        $classLikeNames = [
            'anonymous class' => 'Anonymous',
            'class' => 'Foo',
            'enum' => 'Foo',
            'interface' => 'Foo',
        ];

        /** @var array<string,IncludeType> $includes */
        $includes = [
            'require "foo.php";' => IncludeType::Require,
            'require_once "foo.php";' => IncludeType::RequireOnce,
            'include "foo.php";' => IncludeType::Include,
            'include_once "foo.php";' => IncludeType::IncludeOnce,
        ];

        foreach ($classLike as $keyClass => $expected) {
            foreach ($includes as $code => $include) {
                foreach (IncludeType::cases() as $includeType) {
                    if ($includeType === $include) {
                        continue;
                    }

                    yield sprintf('%s - for %s with %s', $keyClass, $includeType->label(), $include->label()) => [
                        sprintf($expected, $code),
                        $classLikeNames[$keyClass],
                        $includeType,
                    ];
                }
            }
        }
    }

    #[DataProvider('getScriptLikeWithoutIncludeProvider')]
    public function testShouldFailToUseIncludeWithScript(
        string $rawClass,
        IncludeType $includeType,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawClass)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseInclude($includeType),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>tmp/run_0.php</promote> must use <fire>%s</fire>',
                $includeType->label(),
            ),
        );
    }

    public static function getScriptLikeWithoutIncludeProvider(): Generator
    {
        /** @var array<string,IncludeType> $includes */
        $includes = [
            'require "foo.php";' => IncludeType::Require,
            'require_once "foo.php";' => IncludeType::RequireOnce,
            'include "foo.php";' => IncludeType::Include,
            'include_once "foo.php";' => IncludeType::IncludeOnce,
        ];

        foreach ($includes as $code => $include) {
            foreach (IncludeType::cases() as $includeType) {
                if ($includeType === $include) {
                    continue;
                }

                yield sprintf('%s with %s', $includeType->label(), $include->label()) => [
                    sprintf('<?php %s', $code),
                    $includeType,
                ];
            }
        }
    }
}
