<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use PhpParser\Modifiers;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use StructuraPhp\Structura\Enums\VisibilityType;

/**
 * A property of a class: declared in its body, or promoted by its constructor.
 */
final readonly class PropertyValueObject
{
    /**
     * @param int $flags php-parser modifiers (`PhpParser\Modifiers`)
     * @param array<array-key, AttributeGroup> $attrGroups attributes of the property, or of the
     *                                                     promoted parameter
     */
    public function __construct(
        public string $name,
        public VisibilityType $visibility,
        public int $line,
        public int $flags,
        public array $attrGroups = [],
    ) {}

    public function hasAttribute(string $name): bool
    {
        foreach ($this->getAttributeNames() as $attributeName) {
            if ($attributeName->toString() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, Name>
     */
    public function getAttributeNames(): array
    {
        $attributeNames = [];
        foreach ($this->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attributeNames[] = $attr->name;
            }
        }

        return $attributeNames;
    }

    public function isPublic(): bool
    {
        return $this->visibility === VisibilityType::Public;
    }

    public function isProtected(): bool
    {
        return $this->visibility === VisibilityType::Protected;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === VisibilityType::Private;
    }

    public function isStatic(): bool
    {
        return ($this->flags & Modifiers::STATIC) !== 0;
    }

    public function isReadonly(): bool
    {
        return ($this->flags & Modifiers::READONLY) !== 0;
    }

    public function isAbstract(): bool
    {
        return ($this->flags & Modifiers::ABSTRACT) !== 0;
    }

    public function isFinal(): bool
    {
        return ($this->flags & Modifiers::FINAL) !== 0;
    }

    public function isPublicSet(): bool
    {
        return ($this->flags & Modifiers::PUBLIC_SET) !== 0;
    }

    public function isProtectedSet(): bool
    {
        return ($this->flags & Modifiers::PROTECTED_SET) !== 0;
    }

    public function isPrivateSet(): bool
    {
        return ($this->flags & Modifiers::PRIVATE_SET) !== 0;
    }
}
