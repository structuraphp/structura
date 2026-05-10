<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToReturnArray;
use StructuraPhp\Structura\Concerns\ExprScript\ThirdPartyAssert;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToReturnArray::class)]
#[CoversMethod(ThirdPartyAssert::class, 'toReturnArray')]
final class ToReturnArrayTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getScriptsReturningArray')]
    public function testToReturnArray(string $raw): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toReturnArray(),
            );

        self::assertRulesPass(
            $rules,
            'to return array',
        );
    }

    public static function getScriptsReturningArray(): Generator
    {
        yield 'return empty array' => ['<?php return [];'];

        yield 'return array with elements' => ['<?php return ["key" => "value"];'];

        yield 'return array shorthand' => ['<?php return [1, 2, 3];'];
    }

    #[DataProvider('getScriptsNotReturningArray')]
    public function testShouldFailToReturnArray(string $raw, string $returnType): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toReturnArray(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>tmp/run_0.php</promote> must return an array but returns <fire>%s</fire>',
                $returnType,
            ),
            1,
        );
    }

    public static function getScriptsNotReturningArray(): Generator
    {
        yield 'return string' => ['<?php return "hello";', 'string'];

        yield 'return integer' => ['<?php return 42;', 'integer'];

        yield 'return float' => ['<?php return 3.14;', 'float'];

        yield 'return function call' => ['<?php return someFunction();', 'function call'];

        yield 'return method call' => ['<?php return $obj->method();', 'method call'];

        yield 'return variable' => ['<?php $x = "test"; return $x;', 'variable'];

        yield 'return new object' => ['<?php return new stdClass();', 'object instance'];
    }

    #[DataProvider('getScriptsWithoutReturn')]
    public function testShouldFailWhenNoReturn(string $raw): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toReturnArray(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>tmp/run_0.php</promote> must return an array',
            0,
        );
    }

    public static function getScriptsWithoutReturn(): Generator
    {
        yield 'no return statement' => ['<?php echo "hello";'];

        yield 'only function definition' => ['<?php function foo() { return []; }'];

        yield 'empty script' => ['<?php'];
    }
}
