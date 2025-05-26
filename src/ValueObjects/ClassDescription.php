<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\TraitUse;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Enums\DependenciesType;

final class ClassDescription
{
    /** @var array<int,string> */
    private array $dependencies = [];

    private ?string $fileBasename = null;

    /**
     * @param array<array-key, AttributeGroup> $attrGroups
     * @param null|Identifier $scalarType enum type
     * @param array<array-key,Name> $interfaces
     * @param null|array<Name>|Name $extends
     * @param array<TraitUse> $traits
     * @param null|array<ClassMethod> $methods
     */
    public function __construct(
        public readonly ?string $name,
        public readonly array $attrGroups,
        public readonly int $lines,
        public readonly ?string $namespace,
        public readonly ?Identifier $scalarType,
        public readonly ?array $interfaces,
        public readonly null|array|Name $extends,
        public readonly array $traits,
        public readonly ?int $flags,
        public readonly ClassType $classType,
        public readonly ?array $methods,
        public readonly ?Declare_ $declare,
    ) {}

    /**
     * @return array<int,string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param array<int,string> $dependencies
     */
    public function setDependencies(array $dependencies): self
    {
        $this->dependencies = $dependencies;

        return $this;
    }

    public function getFileBasename(): ?string
    {
        return $this->fileBasename;
    }

    public function setFilePathname(?string $fileBasename): self
    {
        $this->fileBasename = $fileBasename;

        return $this;
    }

    public function isExtendable(): bool
    {
        return \in_array(
            $this->classType,
            [ClassType::Class_, ClassType::AnonymousClass_, ClassType::Interface_],
            true,
        );
    }

    public function isInterfaceable(): bool
    {
        return \in_array(
            $this->classType,
            [ClassType::Class_, ClassType::AnonymousClass_, ClassType::Enum_],
            true,
        );
    }

    public function isAnonymous(): bool
    {
        return $this->classType === ClassType::AnonymousClass_;
    }

    public function hasAttribute(string $name): bool
    {
        if ($this->attrGroups === []) {
            return false;
        }

        foreach ($this->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attrs) {
                if ($attrs->name->toString() === $name) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasMethods(string $name): bool
    {
        if ($this->methods === null) {
            return false;
        }

        foreach ($this->methods as $method) {
            if ($method->name->name === $name) {
                return true;
            }
        }

        return false;
    }

    public function hasTrait(string $name): bool
    {
        if ($this->traits === []) {
            return false;
        }

        foreach ($this->traits as $traitsUse) {
            foreach ($traitsUse->traits as $trait) {
                if ($trait->toString() === $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public function getExtendNames(): array
    {
        if ($this->extends instanceof Name) {
            return [$this->extends->toString()];
        }

        if ($this->extends === null) {
            return [];
        }

        $extends = [];
        foreach ($this->extends as $extend) {
            $extends[] = $extend->toString();
        }

        return $extends;
    }

    public function hasInterface(string $name): bool
    {
        if ($this->interfaces === [] || $this->interfaces === null) {
            return false;
        }

        foreach ($this->interfaces as $interface) {
            if ($interface->toString() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int,string>
     */
    public function getTraitNames(): array
    {
        $traitNames = [];
        foreach ($this->traits as $traitsUse) {
            foreach ($traitsUse->traits as $trait) {
                $traitNames[] = $trait->toString();
            }
        }

        return $traitNames;
    }

    /**
     * @return array<int,string>
     */
    public function getInterfaceNames(): array
    {
        if ($this->interfaces === [] || $this->interfaces === null) {
            return [];
        }

        $interfaces = [];
        foreach ($this->interfaces as $interface) {
            $interfaces[] = $interface->toString();
        }

        return $interfaces;
    }

    /**
     * @return array<int, string>
     */
    public function getAttributeNames(): array
    {
        $attributeNames = [];
        foreach ($this->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attributeNames[] = $attr->name->toString();
            }
        }

        return $attributeNames;
    }

    public function hasDeclare(
        string $key,
        string $value,
    ): bool {
        if (!$this->declare instanceof Declare_) {
            return false;
        }

        foreach ($this->declare->declares as $declare) {
            if (
                $declare->key->name === $key
                && $declare->value->getAttribute('rawValue') === $value
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,string> $patterns
     *
     * @return array<int,string>
     */
    public function getDependenciesByPatterns(
        array $patterns,
        DependenciesType $type = DependenciesType::All,
    ): array {
        $matches = [];
        if ($patterns === []) {
            return [];
        }

        $pattern = implode('|', $patterns);

        /** @var array<int,string>|false $match */
        $match = preg_grep(
            '/^' . $this->customPregQuote($pattern) . '$/',
            $this->getDependenciesByType($type),
        );

        if ($match !== false) {
            return array_merge($matches, $match);
        }

        return $matches;
    }

    /**
     * @param array<int,string> $allowedCharacters
     */
    private function customPregQuote(
        string $subject,
        array $allowedCharacters = ['^', '$', '\\'],
    ): string {
        $mapping = [];
        foreach ($allowedCharacters as $char) {
            $mapping[$char] = '\\' . $char;
        }

        return strtr($subject, $mapping);
    }

    /**
     * @return array<int,string>
     */
    private function getDependenciesByType(DependenciesType $dependenciesType): array
    {
        return match ($dependenciesType) {
            DependenciesType::All => $this->getDependencies(),
            DependenciesType::Attributes => $this->getAttributeNames(),
            DependenciesType::Traits => $this->getTraitNames(),
            DependenciesType::Extends => $this->getExtendNames(),
            DependenciesType::Interfaces => $this->getInterfaceNames(),
        };
    }
}
