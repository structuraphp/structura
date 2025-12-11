<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Expr;

trait ShadowDependencies
{
    /** @var array<int,array<int,class-string>> */
    private array $attributDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $extendDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $implementDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $traitDependencies = [];

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getExtends(): array
    {
        return $this->extendDependencies;
    }

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getImplements(): array
    {
        return $this->implementDependencies;
    }

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getTraits(): array
    {
        return $this->traitDependencies;
    }

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getAttributs(): array
    {
        return $this->attributDependencies;
    }

    /**
     * @param class-string $name
     */
    public function setExtends(string $name): void
    {
        $this->extendDependencies[] = [$name];
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function setImplements(array|string $names): void
    {
        $this->implementDependencies[] = (array) $names;
    }

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function setTraits(array|string $names): void
    {
        $this->traitDependencies[] = (array) $names;
    }

    /**
     * @param class-string $name
     */
    public function setAttributs(string $name): void
    {
        $this->attributDependencies[] = [$name];
    }
}
