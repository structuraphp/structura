<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use StructuraPhp\Structura\Enums\DescriptorType;
use StructuraPhp\Structura\Services\ParseService;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use Symfony\Component\Finder\Finder;

/**
 * Frozen corpus shared by every benchmark.
 *
 * The files under Fixture/ must not be modified: any change invalidates the
 * comparison against a previously stored baseline.
 */
final readonly class Corpus
{
    public static function dir(): string
    {
        return __DIR__ . '/Fixture';
    }

    public static function finder(): Finder
    {
        return Finder::create()
            ->files()
            ->followLinks()
            ->sortByName()
            ->name('*.php')
            ->in(self::dir());
    }

    /**
     * @return array<int, ClassDescription>
     */
    public static function classDescriptions(): array
    {
        $parseService = new ParseService(DescriptorType::ClassLike);

        $descriptions = [];
        foreach ($parseService->parse(self::finder()) as $description) {
            if ($description instanceof ClassDescription) {
                $descriptions[] = $description;
            }
        }

        return $descriptions;
    }

    /**
     * @return array<int, ScriptDescription>
     */
    public static function scriptDescriptions(): array
    {
        $parseService = new ParseService(DescriptorType::Script);

        return iterator_to_array($parseService->parse(self::finder()), false);
    }
}
