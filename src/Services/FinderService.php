<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use Symfony\Component\Finder\Finder;

class FinderService
{
    /** @var null|array<int,class-string<TestBuilder>> */
    private static ?array $loadedClasses = null;

    public function __construct(
        private readonly ConfigValueObject $config,
        private readonly ?string $testSuite = null,
    ) {}

    /**
     * @return array<int,class-string<TestBuilder>>
     */
    public function getClassTests(): array
    {
        $testSuites = is_string($this->testSuite)
            ? ($this->config->testSuites[$this->testSuite] ?? null)
            : $this->config->testSuites;

        if ($testSuites === [] || $testSuites === null) {
            return [];
        }

        if (self::$loadedClasses !== null) {
            return self::$loadedClasses;
        }

        $baseClasses = get_declared_classes();
        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->sortByName()
            ->name('Test*.php')
            ->in($testSuites);

        foreach ($finder as $file) {
            require_once $file->getRealPath();
        }

        /** @var array<int,class-string> $classes */
        $classes = array_diff(get_declared_classes(), $baseClasses);

        /** @var array<int,class-string<TestBuilder>> $instances */
        $instances = array_filter(
            $classes,
            static fn (string $class): bool => in_array(
                TestBuilder::class,
                class_parents($class),
                true,
            ),
        );

        return self::$loadedClasses ??= $instances;
    }
}
