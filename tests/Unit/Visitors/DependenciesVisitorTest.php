<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Visitors;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Tests\Helper\ParserHelper;
use StructuraPhp\Structura\Visitors\DependenciesVisitor;

#[CoversClass(DependenciesVisitor::class)]
final class DependenciesVisitorTest extends TestCase
{
    use ParserHelper;

    public function testClassDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse($visitor, $this->getClassLikeProvider());

        self::assertSame(
            [
                'Dependency0\Bar',
                'Dependency1\Baz',
                'Dependency\Dependency2',
                'Dependency\Dependency3',
                'Dependency4\Shadow2',
                // regular dependencies
                'Dependency11',
                'Dependency11_1',
                'Dependency11_2',
                'Dependency11_3',
                'Dependency12',
                // 'Dependency13', => attribut
                'Dependency14',
                'Dependency15',
                // 'Dependency16', => attribut
                'Dependency17',
                'Dependency18',
                'Dependency19',
                'Dependency20_0',
                'Dependency20_1',
                'Dependency20_2',
                'Dependency20_3',
                'Dependency21',
                'Dependency22',
                'Dependency23',
                'Dependency24',
                'Dependency25',
                'Dependency\Shadow1\Dependency26',
                'Dependency4\Shadow2\Dependency27',
            ],
            array_keys($visitor->getDependencies()),
        );
    }

    public function testDocBlockDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse($visitor, $this->getDocBlockProvider());

        // consume main deps to trigger shadow cleanup
        $visitor->getDependencies();

        self::assertSame(
            [
                'Acme\DocBlock1',
                'Dependency\DocBlock2',
                'Dependency\DocBlock3',
                'Dependency\DocBlock4',
                'Dependency\DocBlock5',
                'Dependency\DocBlock6',
                'Dependency\DocBlock7',
                'Dependency\Shadow1\Nested',
                'Dependency\DocBlock8',
                'Dependency\DocBlock9',
                'Dependency\DocBlock11',
                'Dependency\DocBlock10',
                'Dependency\DocBlock12',
                'Acme\DocBlock13',
            ],
            array_keys($visitor->getDocBlockDependencies()),
        );
    }

    public function testDocBlockDependenciesAreNotInMainDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse($visitor, $this->getDocBlockProvider());

        $mainDeps = array_keys($visitor->getDependencies());

        foreach (array_keys($visitor->getDocBlockDependencies()) as $docBlockDep) {
            self::assertNotContains(
                $docBlockDep,
                $mainDeps,
                sprintf('DocBlock dependency "%s" should not appear in main dependencies', $docBlockDep),
            );
        }
    }

    public function testDocBlockShadowDependenciesAreResolved(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\DocBlockShadow1;

            class Foo
            {
                /** @var DocBlockShadow1\Dependency1 */
                private mixed $prop;
            }
            PHP,
        );

        $visitor->getDependencies();

        self::assertSame(
            ['Dependency\DocBlockShadow1\Dependency1'],
            array_keys($visitor->getDocBlockDependencies()),
        );
    }

    public function testExtendsAreNotDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\Dependency1;

            class Foo extends Dependency1
            {
            }
            PHP
        );

        self::assertSame([], array_keys($visitor->getDependencies()));
    }

    public function testTraitsAreNotDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\Dependency1;
            use Dependency\Shadow1;

            class Foo
            {
                use Dependency1, Shadow1\Dependency2, \Dependency3;
            }
            PHP
        );

        self::assertSame([], array_keys($visitor->getDependencies()));
    }

    public function testImplementsAreNotDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\Dependency1;
            use Dependency\Shadow1;

            class Foo implements Dependency1, Shadow1\Dependency2, \Dependency3
            {
            }
            PHP
        );

        self::assertSame([], array_keys($visitor->getDependencies()));
    }

    public function testUseConstAreNotDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use const Dependency\Const1, Dependency\Const2;
            use const Dependency\{Const3, Const4};

            class Foo {}
            PHP
        );

        self::assertSame([], array_keys($visitor->getDependencies()));
    }

    public function testUseFunctionAreNotDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use function Dependency\Func1, Dependency\Func2;

            class Foo {}
            PHP
        );

        self::assertSame([], array_keys($visitor->getDependencies()));
    }

    public function testUseAttributesAreNotDependencies(): void
    {
        $visitor = new DependenciesVisitor();

        $this->traverse(
            $visitor,
            <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\Dependency1;

            #[Dependency1]
            class Foo
            {
            }
            PHP
        );

        self::assertSame(
            [],
            array_keys($visitor->getDependencies()),
        );
    }

    private function getClassLikeProvider(): string
    {
        return <<<'PHP'
            <?php

            namespace Acme;

            use Dependency0\Bar, Dependency1\Baz as AliasBaz;
            use Dependency\{Dependency2, Dependency3};
            use Dependency\Shadow1;
            use Dependency4\Shadow2;
            use Dependency\DocBlock1;
            use Dependency\DocBlock2;
            use Dependency\DocBlock3;

            use const Dependency\Const1, Dependency\Const2;
            use const Dependency\{Const3, Const4};

            use function Dependency\Func1, Dependency\Func2;

            /**
             * @mixin DocBlock1
             */
            class Foo extends \Dependency5 implements \Dependency6, Shadow1\Dependency7
            {
                use \Dependency8, \Dependency9;
                use \Dependency10;

                /** @var DocBlock2 */
                private Bar $bar;
                private AliasBaz $barAlias;
                private \Dependency11 $dateTime;
                private \Dependency11_1 $hook {
                    get {
                        new \Dependency11_2();
                    }
                    set {
                        new \Dependency11_3();
                    }
                }

                public function __construct(
                    \Dependency12 $arrayAccess,
                    #[\Dependency13]
                    private string $number,
                ) {
                    new \Dependency14($this->number);
                }

                /** @return DocBlock3|null */
                #[\Dependency16]
                public function __toString(): \Dependency15 {
                    \Dependency17::class . ' ' . $this->number->toString();

                    $this->arrayAccess['foo'] ?? throw new \Dependency18();

                    $this->arrayAccess['foo'] instanceof \Dependency19;

                    new class extends \Dependency20_0 implements \Dependency20_1 {
                        use \Dependency20_2;

                        #[\Dependency20_3]
                        private int $i;
                    };

                    fn(\Dependency22 $foo): \Dependency21 => $foo;

                    function(\Dependency24 $foo): \Dependency23 {};

                    try {

                    } catch (\Dependency25 $e) {

                    }

                    Shadow1\Dependency26::class;

                    Shadow2\Dependency27::class;
                    Shadow2::class;
                }
            }
            PHP;
    }

    private function getDocBlockProvider(): string
    {
        return <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\DocBlock1 as AliasDocBlock1;
            use Dependency\DocBlock2;
            use Dependency\DocBlock3;
            use Dependency\DocBlock4;
            use Dependency\DocBlock5;
            use Dependency\DocBlock6;
            use Dependency\DocBlock7;
            use Dependency\Shadow1;
            use Dependency\DocBlock8;
            use Dependency\DocBlock9;
            use Dependency\DocBlock10;
            use Dependency\{DocBlock11, DocBlock12};

            /**
             * @mixin DocBlock1
             */
            class Foo
            {
                /** @var DocBlock2 */
                private mixed $prop1;

                /** @var DocBlock3|null */
                private mixed $prop2;

                /** @var array<string, DocBlock4> */
                private array $prop3;

                /** @var ?DocBlock5 */
                private mixed $prop4;

                /**
                 * @param DocBlock6 $a
                 * @return DocBlock7
                 * @throws Shadow1\Nested
                 */
                public function bar(mixed $a): mixed
                {
                    return $a;
                }

                /** @var DocBlock8&DocBlock9 */
                private mixed $prop5;

                /**
                 * @param callable(DocBlock10): DocBlock11 $fn
                 */
                public function baz(mixed $fn): void {}

                /**
                 * @param array{key: DocBlock12} $data
                 * @return DocBlock13 
                 */
                public function qux(array $data): mixed {}
            }
            PHP;
    }
}
