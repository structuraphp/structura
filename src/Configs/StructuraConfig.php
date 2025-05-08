<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Configs;

use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;
use Symfony\Component\Finder\Finder;

class StructuraConfig
{
    /** @var array<int,class-string<TestBuilder>> */
    private array $rules = [];

    /** @var array<int,string> */
    private array $extensions;

    private ?RootNamespaceValueObject $archiRootNamespace = null;

    public static function make(): self
    {
        return new self();
    }

    /**
     * @param array<int,string> $extensions
     */
    public function fileExtensions(array $extensions): self
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @param class-string<TestBuilder> $classes
     */
    public function rules(string ...$classes): self
    {
        $this->rules = array_merge($this->rules, array_values($classes));

        return $this;
    }

    /**
     * @param class-string<TestBuilder> $class
     */
    public function rule(string $class): self
    {
        $this->rules[] = $class;

        return $this;
    }

    public function archiRootNamespace(string $namespace, string $directory): self
    {
        $this->archiRootNamespace = new RootNamespaceValueObject(
            namespace: $namespace,
            directory: $directory,
        );

        return $this;
    }

    /**
     * @return array<int,class-string<TestBuilder>>
     */
    public function getRules(): array
    {
        $this->setRulesByRootNamespace();

        return $this->rules;
    }

    public function getArchiRootNamespace(): ?RootNamespaceValueObject
    {
        return $this->archiRootNamespace;
    }

    /**
     * @return array<int,string>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    private function setRulesByRootNamespace(): void
    {
        if (!$this->archiRootNamespace instanceof RootNamespaceValueObject) {
            return;
        }

        $directory = $this->archiRootNamespace->directory;
        $namespace = $this->archiRootNamespace->namespace;

        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->sortByName()
            ->name('Test*.php')
            ->in($directory);

        foreach ($finder as $file) {
            $pathName = $file->getPath();

            if (str_starts_with($pathName, $directory)) {
                $className = str_replace([$directory, '/'], [$namespace, '\\'], $pathName);
                $className .= '\\' . pathinfo($file->getPathname(), \PATHINFO_FILENAME);

                if (class_exists($className)) {
                    /** @var class-string<TestBuilder> $className */
                    $this->rule($className);
                }
            }
        }
    }
}
