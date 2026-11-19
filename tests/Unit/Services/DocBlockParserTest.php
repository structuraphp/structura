<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Services\DocBlockParser;

#[CoversClass(DocBlockParser::class)]
final class DocBlockParserTest extends TestCase
{
    private DocBlockParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DocBlockParser();
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('provideDocBlocks')]
    public function testParse(string $docBlock, array $expected): void
    {
        $result = $this->parser->parse($docBlock);

        self::assertSame($expected, array_keys($result));
    }

    /**
     * @return iterable<string, array{string, array<int, string>}>
     */
    public static function provideDocBlocks(): iterable
    {
        yield 'empty docblock' => [
            '/** */',
            [],
        ];

        yield 'builtin types are case insensitive' => [
            '/** @param String $a @param INT $b */',
            [],
        ];

        yield 'duplicated class is listed once with sequential keys' => [
            "/**\n * @param Foo \$a\n * @param Foo \$b\n * @param Bar \$c\n */",
            ['Foo', 'Bar'],
        ];

        yield 'builtin types only' => [
            '/** @param int $x @return string */',
            [],
        ];

        yield 'single class in @param' => [
            '/** @param Foo $x */',
            ['Foo'],
        ];

        yield 'FQCN in @param' => [
            '/** @param \App\Models\User $user */',
            ['\App\Models\User'],
        ];

        yield 'union type' => [
            '/** @param Foo|Bar $x */',
            ['Foo', 'Bar'],
        ];

        yield 'intersection type' => [
            '/** @param Foo&Bar $x */',
            ['Foo', 'Bar'],
        ];

        yield 'nullable type' => [
            '/** @param ?Foo $x */',
            ['Foo'],
        ];

        yield 'union with null' => [
            '/** @param Foo|null $x */',
            ['Foo'],
        ];

        yield 'generic type' => [
            '/** @param Collection<Bar> $x */',
            ['Collection', 'Bar'],
        ];

        yield 'nested generic' => [
            '/** @param Map<string, Collection<Entity>> $x */',
            ['Map', 'Collection', 'Entity'],
        ];

        yield 'array of class' => [
            '/** @param Foo[] $x */',
            ['Foo'],
        ];

        yield '@return class' => [
            '/** @return Response */',
            ['Response'],
        ];

        yield '@var class' => [
            '/** @var Logger $logger */',
            ['Logger'],
        ];

        yield '@throws class' => [
            '/** @throws InvalidArgumentException */',
            ['InvalidArgumentException'],
        ];

        yield '@extends class' => [
            '/** @extends AbstractController<Request> */',
            ['AbstractController', 'Request'],
        ];

        yield '@implements class' => [
            '/** @implements RepositoryInterface<Entity> */',
            ['RepositoryInterface', 'Entity'],
        ];

        yield '@mixin class' => [
            '/** @mixin Builder */',
            ['Builder'],
        ];

        yield 'multiple tags' => [
            <<<'DOC'
            /**
             * @param Foo $x
             * @param Bar $y
             * @return Baz
             * @throws Qux
             */
            DOC,
            ['Foo', 'Bar', 'Baz', 'Qux'],
        ];

        yield 'deduplicated classes' => [
            <<<'DOC'
            /**
             * @param Foo $x
             * @return Foo
             */
            DOC,
            ['Foo'],
        ];

        yield 'callable with class types' => [
            '/** @param callable(Request): Response $handler */',
            ['Response', 'Request'],
        ];

        yield '@property class' => [
            '/** @property Logger $logger */',
            ['Logger'],
        ];
    }

    #[DataProvider('provideTagVariants')]
    public function testParseReadsEveryTypedTagVariant(string $tag): void
    {
        self::assertSame(['Foo'], array_keys($this->parser->parse(\sprintf('/** %s Foo<Foo> $foo */', $tag))));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideTagVariants(): iterable
    {
        $tags = [
            '@param', '@phpstan-param', '@psalm-param', '@phan-param',
            '@var', '@phpstan-var', '@psalm-var', '@phan-var',
            '@return', '@phpstan-return', '@psalm-return', '@phan-return', '@phan-real-return',
            '@throws', '@phpstan-throws',
            '@mixin', '@phan-mixin',
            '@property', '@property-read', '@property-write',
            '@phpstan-property', '@phpstan-property-read', '@phpstan-property-write',
            '@psalm-property', '@psalm-property-read', '@psalm-property-write',
            '@phan-property', '@phan-property-read', '@phan-property-write',
            '@extends', '@phpstan-extends', '@phan-extends', '@phan-inherits', '@template-extends',
            '@implements', '@phpstan-implements', '@template-implements',
        ];

        foreach ($tags as $tag) {
            yield $tag => [$tag];
        }
    }

    public function testParseIgnoresDocBlockWithoutTypedTag(): void
    {
        self::assertSame([], $this->parser->parse("/**\n * Sends Foo.\n *\n * @since 1.0\n * @see Foo\n */"));
    }

    public function testParseReturnsLineOfTagInDocBlock(): void
    {
        $docBlock = <<<'PHP'
        /**
         * Description.
         *
         * @param Foo $a
         * @return Bar
         */
        PHP;

        self::assertSame(['Foo' => 4, 'Bar' => 5], $this->parser->parse($docBlock));
    }

    public function testParseKeepsLineOfFirstTag(): void
    {
        $docBlock = <<<'PHP'
        /**
         * @param Foo $a
         * @return Foo
         */
        PHP;

        self::assertSame(['Foo' => 2], $this->parser->parse($docBlock));
    }
}
