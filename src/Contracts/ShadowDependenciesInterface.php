<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

interface ShadowDependenciesInterface
{
    /**
     * @return array<int,array<int,class-string>>
     */
    public function getExtends(): array;

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getImplements(): array;

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getTraits(): array;

    /**
     * @return array<int,array<int,class-string>>
     */
    public function getAttributs(): array;

    /**
     * @param class-string $name
     */
    public function setExtends(string $name): void;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function setImplements(array|string $names): void;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function setTraits(array|string $names): void;

    /**
     * @param class-string $name
     */
    public function setAttributs(string $name): void;
}
