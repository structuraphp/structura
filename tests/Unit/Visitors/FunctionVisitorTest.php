<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Visitors;

use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Tests\Helper\ParserHelper;
use StructuraPhp\Structura\Visitors\FunctionVisitor;

/**
 * @coversNothing
 */
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
            $visitor->getDependencies(),
        );
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
}
