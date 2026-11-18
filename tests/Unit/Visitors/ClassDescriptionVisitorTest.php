<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Visitors;

use Generator;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Return_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Enums\VisibilityType;
use StructuraPhp\Structura\Tests\Helper\ParserHelper;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\PropertyValueObject;
use StructuraPhp\Structura\Visitors\ClassDescriptionVisitor;

#[CoversClass(ClassDescriptionVisitor::class)]
final class ClassDescriptionVisitorTest extends TestCase
{
    use ParserHelper;

    public function testProperties(): void
    {
        $raw = <<<'PHP'
            <?php
            class Foo {
                public $a, $b;
                protected static int $c = 0;
                public function bar(int $x) {}
                public function __construct(
                    private readonly int $d,
                    int $e,
                    readonly string $f,
                ) {}
            }
            PHP;

        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, $raw);
        $class = $visitor->getClass();

        self::assertInstanceOf(ClassDescription::class, $class);
        self::assertSame(
            [
                ['a', VisibilityType::Public, 3, false, false],
                ['b', VisibilityType::Public, 3, false, false],
                ['c', VisibilityType::Protected, 4, true, false],
                ['d', VisibilityType::Private, 7, false, true],
                ['f', VisibilityType::Public, 9, false, true],
            ],
            array_map(
                static fn (PropertyValueObject $property): array => [
                    $property->name,
                    $property->visibility,
                    $property->line,
                    $property->isStatic(),
                    $property->isReadonly(),
                ],
                $class->properties,
            ),
        );
    }

    public function testPropertyAttributes(): void
    {
        $raw = <<<'PHP'
            <?php
            namespace App;
            use App\Attributes\Inject;
            class Foo {
                #[Inject, \Deprecated]
                public $a;
                public $b;
                public function __construct(#[Inject] private int $c) {}
            }
            PHP;

        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, $raw);
        $class = $visitor->getClass();

        self::assertInstanceOf(ClassDescription::class, $class);
        self::assertSame(
            [
                'a' => ['App\Attributes\Inject', 'Deprecated'],
                'b' => [],
                'c' => ['App\Attributes\Inject'],
            ],
            array_combine(
                array_map(
                    static fn (PropertyValueObject $property): string => $property->name,
                    $class->properties,
                ),
                array_map(
                    static fn (PropertyValueObject $property): array => array_map(
                        static fn (Name $name): string => $name->toString(),
                        $property->getAttributeNames(),
                    ),
                    $class->properties,
                ),
            ),
        );
    }

    public function testPropertiesAreResetBetweenFiles(): void
    {
        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, '<?php class Foo { public int $a = 0; }');

        $this->traverse($visitor, '<?php class Bar {}');
        $class = $visitor->getClass();

        self::assertInstanceOf(ClassDescription::class, $class);
        self::assertSame([], $class->properties);
    }

    #[DataProvider('getClassTypeProvider')]
    public function testClassType(string $raw, ClassType $expected): void
    {
        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, $raw);

        self::assertSame($expected, $visitor->getClass()?->classType);
    }

    public static function getClassTypeProvider(): Generator
    {
        yield 'named class' => ['<?php class Foo {}', ClassType::Class_];

        yield 'returned anonymous class' => ['<?php return new class {};', ClassType::AnonymousClass_];

        yield 'assigned anonymous class' => ['<?php $foo = new class {};', ClassType::Class_];
    }

    public function testRootReturnIsOnlyTheFileReturn(): void
    {
        $visitor = new ClassDescriptionVisitor();

        $this->traverse($visitor, '<?php return new class {};');
        self::assertInstanceOf(Return_::class, $visitor->getClass()?->rootReturn);

        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, '<?php class Foo { public function bar() { return 1; } }');
        self::assertNull($visitor->getClass()?->rootReturn);
    }

    public function testNestedAnonymousClassesDoNotReplaceTheMainClass(): void
    {
        $raw = <<<'PHP'
            <?php
            class Foo {
                public function bar() {
                    new class {};
                    new class {};
                }
            }
            PHP;

        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, $raw);
        $class = $visitor->getClass();

        self::assertSame('Foo', $class?->name);
        self::assertSame(ClassType::Class_, $class->classType);
        self::assertCount(2, $class->anonymousClasses);
    }

    public function testSecondNamedClassIsNotAnAnonymousClass(): void
    {
        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, '<?php class Foo {} class Bar {}');

        self::assertSame([], $visitor->getClass()?->anonymousClasses);
    }

    public function testScriptWithoutClassHasNoLine(): void
    {
        $visitor = new ClassDescriptionVisitor();
        $this->traverse($visitor, "<?php\n\necho 1;");

        self::assertSame(0, $visitor->getClass()?->lines);
    }
}
