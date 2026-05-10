<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToUseInclude;
use StructuraPhp\Structura\Concerns\ExprScript\ThirdPartyAssert;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToUseInclude::class)]
#[CoversMethod(ThirdPartyAssert::class, 'toUseInclude')]
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
        IncludeType $actualIncludeType,
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
                'Resource <promote>%s</promote> must use <promote>%s</promote> but uses <fire>%s</fire>',
                $name,
                $includeType->label(),
                $actualIncludeType->label(),
            ),
        );
    }

    public static function getClassLikeWithoutIncludeProvider(): Generator
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
                foreach (IncludeType::cases() as $includeType) {
                    if ($includeType === $include) {
                        continue;
                    }

                    yield sprintf('%s - for %s with %s', $keyClass, $includeType->label(), $include->label()) => [
                        sprintf($expected, $code),
                        $classLikeNames[$keyClass],
                        $includeType,
                        $include,
                    ];
                }
            }
        }
    }

    #[DataProvider('getScriptLikeWithoutIncludeProvider')]
    public function testShouldFailToUseIncludeWithScript(
        string $rawClass,
        IncludeType $includeType,
        IncludeType $actualIncludeType,
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
                'Resource <promote>tmp/run_0.php</promote> must use <promote>%s</promote> but uses <fire>%s</fire>',
                $includeType->label(),
                $actualIncludeType->label(),
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
                    $include,
                ];
            }
        }
    }

    #[DataProvider('getScriptWithPathPatternProvider')]
    public function testToUseIncludeWithPathPatternPasses(
        string $rawScript,
        IncludeType $includeType,
        string $pathPattern,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseInclude($includeType, $pathPattern),
            );

        self::assertRulesPass(
            $rules,
            sprintf(
                'to use <promote>%s</promote> with path matching <promote>%s</promote>',
                $includeType->label(),
                $pathPattern,
            ),
        );
    }

    public static function getScriptWithPathPatternProvider(): Generator
    {
        yield 'literal path match' => [
            '<?php require "vendor/autoload.php";',
            IncludeType::Require,
            'vendor/autoload.php',
        ];

        yield 'glob pattern match' => [
            '<?php require_once "src/config/app.php";',
            IncludeType::RequireOnce,
            '*/config/*.php',
        ];

        yield '__DIR__ concatenation' => [
            '<?php require __DIR__ . "/vendor/autoload.php";',
            IncludeType::Require,
            '*/vendor/autoload.php',
        ];

        yield 'dirname(__FILE__)' => [
            '<?php require dirname(__FILE__) . "/bootstrap.php";',
            IncludeType::Require,
            '*/bootstrap.php',
        ];

        yield 'dirname(__FILE__, 2)' => [
            '<?php require dirname(__FILE__, 2) . "/config/app.php";',
            IncludeType::Require,
            '*/config/app.php',
        ];

        yield 'dirname(__DIR__, 2)' => [
            '<?php require dirname(__DIR__, 2) . "/vendor/autoload.php";',
            IncludeType::Require,
            '*/vendor/autoload.php',
        ];

        yield 'dirname(__FILE__, 3)' => [
            '<?php require dirname(__FILE__, 3) . "/bootstrap.php";',
            IncludeType::Require,
            '*/bootstrap.php',
        ];
    }

    #[DataProvider('getScriptWithPathPatternViolationProvider')]
    public function testToUseIncludeWithPathPatternViolates(
        string $rawScript,
        IncludeType $includeType,
        string $pathPattern,
        string $expectedViolation,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseInclude($includeType, $pathPattern),
            );

        self::assertRulesViolation($rules, $expectedViolation);
    }

    public static function getScriptWithPathPatternViolationProvider(): Generator
    {
        yield 'path does not match pattern' => [
            '<?php require "other/path.php";',
            IncludeType::Require,
            'vendor/*',
            'Resource <promote>tmp/run_0.php</promote> uses path <fire>other/path.php</fire> which does not match pattern <promote>vendor/*</promote>',
        ];

        yield 'dynamic path (variable) is a violation' => [
            '<?php require $path;',
            IncludeType::Require,
            'vendor/*',
            'Resource <promote>tmp/run_0.php</promote> uses a dynamic path which cannot be verified against pattern <fire>vendor/*</fire>',
        ];

        yield 'dirname(__FILE__, 2) path does not match pattern' => [
            '<?php require dirname(__FILE__, 2) . "/config/app.php";',
            IncludeType::Require,
            'vendor/*',
            'Resource <promote>tmp/run_0.php</promote> uses path <fire>./config/app.php</fire> which does not match pattern <promote>vendor/*</promote>',
        ];

        yield 'dirname(__DIR__, 2) path does not match pattern' => [
            '<?php require dirname(__DIR__, 2) . "/vendor/autoload.php";',
            IncludeType::Require,
            'config/*',
            'Resource <promote>tmp/run_0.php</promote> uses path <fire>./vendor/autoload.php</fire> which does not match pattern <promote>config/*</promote>',
        ];
    }

    #[DataProvider('getScriptWithPathResolverPassesProvider')]
    public function testToUseIncludeWithPathResolverPasses(
        string $rawScript,
        IncludeType $includeType,
        string $pathPattern,
        string $resolverName,
        string $resolverPath,
    ): void {
        $ruleBuilder = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseInclude($includeType, $pathPattern),
            );

        $ruleBuilder->setPathResolvers([$resolverName => $resolverPath]);

        self::assertRulesPass(
            $ruleBuilder,
            sprintf(
                'to use <promote>%s</promote> with path matching <promote>%s</promote>',
                $includeType->label(),
                $pathPattern,
            ),
        );
    }

    public static function getScriptWithPathResolverPassesProvider(): Generator
    {
        yield 'base_path() with no arguments matches registered path' => [
            '<?php require base_path() . "/vendor/autoload.php";',
            IncludeType::Require,
            '*/vendor/autoload.php',
            'base_path',
            '/var/www',
        ];

        yield 'app_path() — arguments are ignored, concatenation resolves final path' => [
            '<?php require app_path() . "/bootstrap.php";',
            IncludeType::Require,
            '*/bootstrap.php',
            'app_path',
            '/var/www/app',
        ];

        yield 'custom_resolver() concatenated' => [
            '<?php require_once custom_resolver() . "/config/app.php";',
            IncludeType::RequireOnce,
            '*/config/app.php',
            'custom_resolver',
            '/srv/app',
        ];
    }

    #[DataProvider('getScriptWithPathResolverViolationProvider')]
    public function testToUseIncludeWithPathResolverViolates(
        string $rawScript,
        IncludeType $includeType,
        string $pathPattern,
        string $resolverName,
        string $resolverPath,
        string $expectedViolation,
    ): void {
        $ruleBuilder = $this
            ->allScripts()
            ->fromRaw($rawScript)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseInclude($includeType, $pathPattern),
            );

        $ruleBuilder->setPathResolvers([$resolverName => $resolverPath]);

        self::assertRulesViolation($ruleBuilder, $expectedViolation);
    }

    public static function getScriptWithPathResolverViolationProvider(): Generator
    {
        yield 'path does not match registered resolver path' => [
            '<?php require base_path() . "/vendor/autoload.php";',
            IncludeType::Require,
            'config/*',
            'base_path',
            '/var/www',
            'Resource <promote>tmp/run_0.php</promote> uses path <fire>/var/www/vendor/autoload.php</fire> which does not match pattern <promote>config/*</promote>',
        ];

        yield 'unknown function is treated as dynamic path — violation' => [
            '<?php require unknown_fn() . "/vendor/autoload.php";',
            IncludeType::Require,
            '*/vendor/autoload.php',
            'base_path',
            '/var/www',
            'Resource <promote>tmp/run_0.php</promote> uses a dynamic path which cannot be verified against pattern <fire>*/vendor/autoload.php</fire>',
        ];
    }

    public function testAddPathResolverWithDirnameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"dirname"');

        StructuraConfig::make()->addPathResolver('dirname', '/some/path');
    }
}
