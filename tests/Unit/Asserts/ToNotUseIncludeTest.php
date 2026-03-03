<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotUseInclude;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotUseInclude::class)]
#[CoversMethod(Expr::class, 'toNotUseInclude')]
final class ToNotUseIncludeTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutIncludeProvider')]
    public function testToNotUseRequireWithClass(
        string $rawClass,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseInclude(),
            );

        self::assertRulesPass(
            $rules,
            'to not use <promote>include, include_once, require, require_once</promote>',
        );
    }

    public static function getClassLikeWithoutIncludeProvider(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];
    }

    public function testToNotUseRequireWithScript(): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw('<?php ')
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotUseInclude(),
            );

        self::assertRulesPass(
            $rules,
            'to not use <promote>include, include_once, require, require_once</promote>',
        );
    }

    #[DataProvider('getClassLikeWithIncludeProvider')]
    public function testShouldFailToNotUseRequireWithClass(
        string $rawClass,
        string $name,
        IncludeType $includeType,
    ): void {
        $rules = $this
            ->allClasses()
            ->fromRaw($rawClass)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseInclude(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not use anything but use <fire>%s</fire>',
                $name,
                $includeType->label(),
            ),
        );
    }

    public static function getClassLikeWithIncludeProvider(): Generator
    {
        $classLike = [
            'anonymous class' => '<?php return new class { public function bar() { %s } };',
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
                yield sprintf('%s - with %s', $keyClass, $include->label()) => [
                    sprintf($expected, $code),
                    $classLikeNames[$keyClass],
                    $include,
                ];
            }
        }
    }

    #[DataProvider('getScriptLikeWithIncludeProvider')]
    public function testShouldFailToNotUseIncludeWithScript(
        string $rawClass,
        IncludeType $includeType,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawClass)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotUseInclude(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>tmp/run_0.php</promote> must not use anything but use <fire>%s</fire>',
                $includeType->label(),
            ),
            1,
        );
    }

    public static function getScriptLikeWithIncludeProvider(): Generator
    {
        /** @var array<string,IncludeType> $includes */
        $includes = [
            'require "foo.php";' => IncludeType::Require,
            'require_once "foo.php";' => IncludeType::RequireOnce,
            'include "foo.php";' => IncludeType::Include,
            'include_once "foo.php";' => IncludeType::IncludeOnce,
        ];

        foreach ($includes as $code => $include) {
            yield sprintf('with %s', $include->label()) => [
                sprintf('<?php %s', $code),
                $include,
            ];
        }
    }
}
