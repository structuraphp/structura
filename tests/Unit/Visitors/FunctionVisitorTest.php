<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Visitors;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Tests\Helper\ParserHelper;
use StructuraPhp\Structura\Visitors\FunctionVisitor;

#[CoversClass(FunctionVisitor::class)]
class FunctionVisitorTest extends TestCase
{
    use ParserHelper;

    public function testFunctionDependencies(): void
    {
        $visitor = new FunctionVisitor();

        $this->traverse($visitor, $this->getUseDependenciesFunction());

        self::assertSame(
            [
                'func1',
                'func2',
                'func3',
                'Dependency\func4',
                'func5',
                'func6',
                'Dependency\Shadow1\func7',
                'Dependency\Shadow2\func8',
                'Dependency\Shadow2',
            ],
            array_keys($visitor->getDependencies()),
        );
    }

    public function testFunctionDependencyLineIsFirstOccurrence(): void
    {
        $visitor = new FunctionVisitor();
        $this->traverse($visitor, <<<'PHP'
        <?php
        use function Dep\Shadow;

        foo();
        foo();
        Shadow\bar();
        PHP);

        self::assertSame(
            ['foo' => 4, 'Dep\Shadow\bar' => 6],
            $visitor->getDependencies(),
        );
    }

    public function testImportedFunctionLineIsUseLine(): void
    {
        $visitor = new FunctionVisitor();
        $this->traverse($visitor, <<<'PHP'
        <?php
        use function Dep\foo;

        foo();
        PHP);

        self::assertSame(['Dep\foo' => 2], $visitor->getDependencies());
    }

    public function testImportedTwiceFunctionLineIsFirstUseLine(): void
    {
        $visitor = new FunctionVisitor();
        $this->traverse($visitor, <<<'PHP'
        <?php
        use function Dep\foo;
        use function Dep\foo as bar;
        PHP);

        self::assertSame(['Dep\foo' => 2], $visitor->getDependencies());
    }

    public function getUseDependenciesFunction(): string
    {
        return <<<'PHP'
        <?php
        
        use function func1;
        use function func2, func3;
        use function Dependency\func4;
        use function Dependency\Shadow1;
        use function Dependency\Shadow2;

        func5();

        class Foo {
            public function __construct() {
                func6();
                func6();
                Shadow1\func7();
                Shadow2\func8();
                Shadow2();
                // Expr !== FullyQualified
                $foo(...);
            }
        }
        PHP;
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('getShadowProvider')]
    public function testShadow(string $raw, array $expected): void
    {
        $visitor = new FunctionVisitor();
        $this->traverse($visitor, $raw);

        self::assertSame($expected, array_keys($visitor->getDependencies()));
    }

    /**
     * @return Generator<string, array{string, array<int, string>}>
     */
    public static function getShadowProvider(): Generator
    {
        yield 'every call through the alias is resolved' => [
            '<?php use function Dep\Shadow; Shadow\foo(); Shadow\bar();',
            ['Dep\Shadow\foo', 'Dep\Shadow\bar'],
        ];

        yield 'function sharing a prefix without separator' => [
            '<?php use function Foo\bar; Foo\barbaz();',
            ['Foo\bar', 'Foo\barbaz'],
        ];
    }
}
