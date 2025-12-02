<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;
use Symfony\Component\Finder\Finder;

class FinderService
{
    public function __construct(
        private ConfigValueObject $config,
    ) {}

    /**
     * @return array<int,class-string<TestBuilder>>
     */
    public function getClassTests(): array
    {
        if (!$this->config->rootNamespace instanceof RootNamespaceValueObject) {
            return [];
        }

        $directory = $this->config->rootNamespace->directory;
        $namespace = $this->config->rootNamespace->namespace;

        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->sortByName()
            ->name('Test*.php')
            ->in($directory);

        $rules = [];
        foreach ($finder as $file) {
            $pathName = $file->getPath();

            if (str_starts_with($pathName, $directory)) {
                $className = str_replace([$directory, '/'], [$namespace, '\\'], $pathName);
                $className .= '\\' . pathinfo($file->getPathname(), \PATHINFO_FILENAME);

                if (class_exists($className)) {
                    /** @var class-string<TestBuilder> $className */
                    $rules[] = $className;
                }
            }
        }

        return $rules;
    }
}
