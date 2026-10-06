<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\ValueObjects;

use Generator;
use PhpParser\Modifiers;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Enums\VisibilityType;
use StructuraPhp\Structura\ValueObjects\PropertyValueObject;

#[CoversClass(PropertyValueObject::class)]
final class PropertyValueObjectTest extends TestCase
{
    /**
     * @param array<int, string> $expected methods returning true
     */
    #[DataProvider('getPropertyProvider')]
    public function testMethods(VisibilityType $visibility, int $flags, array $expected): void
    {
        $property = new PropertyValueObject('foo', $visibility, 3, $flags);

        $actual = [
            'isPublic' => $property->isPublic(),
            'isProtected' => $property->isProtected(),
            'isPrivate' => $property->isPrivate(),
            'isStatic' => $property->isStatic(),
            'isReadonly' => $property->isReadonly(),
            'isAbstract' => $property->isAbstract(),
            'isFinal' => $property->isFinal(),
            'isPublicSet' => $property->isPublicSet(),
            'isProtectedSet' => $property->isProtectedSet(),
            'isPrivateSet' => $property->isPrivateSet(),
        ];

        self::assertSame($expected, array_keys(array_filter($actual)));

        self::assertSame('foo', $property->name);
        self::assertSame(3, $property->line);
        self::assertSame($flags, $property->flags);
    }

    public static function getPropertyProvider(): Generator
    {
        yield 'public' => [VisibilityType::Public, Modifiers::PUBLIC, ['isPublic']];

        yield 'protected static' => [
            VisibilityType::Protected,
            Modifiers::PROTECTED | Modifiers::STATIC,
            ['isProtected', 'isStatic'],
        ];

        yield 'private readonly' => [
            VisibilityType::Private,
            Modifiers::PRIVATE | Modifiers::READONLY,
            ['isPrivate', 'isReadonly'],
        ];

        yield 'abstract final' => [
            VisibilityType::Public,
            Modifiers::PUBLIC | Modifiers::ABSTRACT | Modifiers::FINAL,
            ['isPublic', 'isAbstract', 'isFinal'],
        ];

        yield 'asymmetric visibility' => [
            VisibilityType::Public,
            Modifiers::PUBLIC
                | Modifiers::PUBLIC_SET
                | Modifiers::PROTECTED_SET
                | Modifiers::PRIVATE_SET,
            ['isPublic', 'isPublicSet', 'isProtectedSet', 'isPrivateSet'],
        ];
    }

    public function testAttributes(): void
    {
        $property = new PropertyValueObject(
            'foo',
            VisibilityType::Public,
            3,
            Modifiers::PUBLIC,
            [
                new AttributeGroup([new Attribute(new FullyQualified('App\Inject'))]),
                new AttributeGroup([
                    new Attribute(new FullyQualified('Deprecated')),
                    new Attribute(new FullyQualified('App\Since')),
                ]),
            ],
        );

        self::assertSame(
            ['App\Inject', 'Deprecated', 'App\Since'],
            array_map(
                static fn (Name $name): string => $name->toString(),
                $property->getAttributeNames(),
            ),
        );
        self::assertTrue($property->hasAttribute('App\Since'));
        self::assertFalse($property->hasAttribute('Since'));
    }

    public function testWithoutAttributes(): void
    {
        $property = new PropertyValueObject('foo', VisibilityType::Public, 3, Modifiers::PUBLIC);

        self::assertSame([], $property->getAttributeNames());
        self::assertFalse($property->hasAttribute('Deprecated'));
    }
}
