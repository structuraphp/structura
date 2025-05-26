<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Services\ParseService;

#[CoversClass(ParseService::class)]
final class ParseServiceTest extends TestCase
{
    private ParseService $parseService;

    protected function setUp(): void
    {
        $this->parseService = new ParseService();
    }

    public function testClassDependencies(): void
    {
        $raw = $this->getClassLikeProvider();

        $generator = $this->parseService->parseRaw($raw);
        $classDescription = iterator_to_array($generator)[0];

        self::assertSame(
            [
                'Dependency0\Bar',
                'Dependency1\Baz',
                'Dependency\Dependency2',
                'Dependency\Dependency3',
                'Dependency4\Shadow2',
                // 'Dependency5', => inheritance
                // 'Dependency6', => interface
                // 'Dependency7', => interface
                // 'Dependency8', => trait
                // 'Dependency9', => trait
                // 'Dependency10', => trait
                'Dependency11',
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
            $classDescription->getDependencies(),
        );
    }

    public function testClassDependencies2(): void
    {
        $raw = $this->getUseDependanciesClass();

        $generator = $this->parseService->parseRaw($raw);
        $classDescription = iterator_to_array($generator)[0];

        self::assertSame(
            [],
            $classDescription->getDependencies(),
        );
    }

    public function getUseDependanciesClass(): string
    {
        return <<<'PHP'
            <?php

            namespace Acme;

            use Dependency\Dependency1;
            use Dependency\Dependency2;
            use Dependency\Dependency3;
            use Dependency\Dependency4;
            use Dependency\Shadow1;

            #[Dependency1]
            class Foo extends Dependency2 implements Dependency3
            {
                use Dependency4, Shadow1\Dependency5; 
            }
            PHP;
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

            use const Dependency\Const1, Dependency\Const2;
            use const Dependency\{Const3, Const4};

            use function Dependency\Func1, Dependency\Func2;

            class Foo extends \Dependency5 implements \Dependency6, Shadow1\Dependency7
            {
                use \Dependency8, \Dependency9;
                use \Dependency10;

                private Bar $bar;
                private AliasBaz $barAlias;
                private \Dependency11 $dateTime;

                public function __construct(
                    \Dependency12 $arrayAccess,
                    #[\Dependency13]
                    private string $number,
                ) {
                    new \Dependency14($this->number);
                }

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
}
