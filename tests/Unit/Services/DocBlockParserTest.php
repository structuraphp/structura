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

        self::assertSame($expected, $result);
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
}
