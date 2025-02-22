<?php

declare(strict_types=1);

namespace Structura\ValueObjects;

use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\TraitUse;
use Structura\Enums\ClassType;

class ClassDescription
{
    /** @var array<int,string> */
    private array $dependencies = [];

    private ?string $fileBasename = null;

    /**
     * @param array<array-key, AttributeGroup> $attrGroups
     * @param Identifier|null $scalarType enum type
     * @param array<array-key,Name> $interfaces
     * @param array<Name>|Name|null $extends
     * @param array<TraitUse> $traits
     * @param array<ClassMethod>|null $methods
     */
    public function __construct(
        public readonly ?string $name,
        public readonly array $attrGroups,
        public readonly int $lines,
        public readonly ?string $namespace,
        public readonly ?Identifier $scalarType,
        public readonly ?array $interfaces,
        public readonly array|Name|null $extends,
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
}
