<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use InvalidArgumentException;
use ParseError;
use StructuraPhp\Structura\ValueObjects\BaselineEntryValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

/**
 * Tolerates the violations recorded in a baseline file and reports the
 * entries that no longer match any violation.
 */
final class BaselineService
{
    /** @var array<string, BaselineEntryValueObject> key => entry */
    private array $entries = [];

    /** @var array<string, BaselineEntryValueObject> entries no violation has matched yet */
    private array $unmatched;

    private int $countTolerated = 0;

    /**
     * @param array<string, array<string, array<string, array<int, string>>>> $entries test => path => rule => identifiers
     */
    public function __construct(
        array $entries,
        private readonly string $baseDirectory,
    ) {
        foreach ($entries as $test => $paths) {
            foreach ($paths as $path => $rules) {
                foreach ($rules as $rule => $identifiers) {
                    foreach ($identifiers as $identifier) {
                        $entry = new BaselineEntryValueObject($test, $path, $rule, $identifier);
                        $this->entries[$entry->key()] = $entry;
                    }
                }
            }
        }

        $this->unmatched = $this->entries;
    }

    public static function fromFile(string $path, string $baseDirectory): self
    {
        if (!is_file($path)) {
            throw new InvalidArgumentException(\sprintf('Baseline file "%s" not found', $path));
        }

        try {
            // a PHP file returns an untyped value: validated below
            $entries = require $path;
        } catch (ParseError $parseError) {
            throw new InvalidArgumentException(
                message: \sprintf('Baseline file "%s" is not valid PHP: %s', $path, $parseError->getMessage()),
                code: $parseError->getCode(),
                previous: $parseError,
            );
        }

        if (!self::isValid($entries)) {
            throw new InvalidArgumentException(
                \sprintf('Baseline file "%s" must return array<string, array<string, array<string, list<string>>>>', $path),
            );
        }

        return new self($entries, $baseDirectory);
    }

    public function isTolerated(SourceTestValueObject $source, ViolationValueObject $violation): bool
    {
        $key = BaselineEntryValueObject::fromViolation($source, $violation, $this->baseDirectory)?->key();
        if ($key === null || !isset($this->entries[$key])) {
            return false;
        }

        unset($this->unmatched[$key]);
        $this->countTolerated++;

        return true;
    }

    public function countTolerated(): int
    {
        return $this->countTolerated;
    }

    /**
     * @return array<int, string>
     */
    public function getStaleWarnings(): array
    {
        $warnings = array_map(
            static fn (BaselineEntryValueObject $entry): string => \sprintf(
                'Baseline entry <promote>%s</promote> for <promote>%s</promote> in <promote>%s</promote> from test <promote>%s</promote> no longer matches any violation',
                $entry->identifier,
                $entry->rule,
                $entry->path,
                $entry->test,
            ),
            $this->unmatched,
        );

        sort($warnings);

        return $warnings;
    }

    /**
     * @phpstan-assert-if-true array<string, array<string, array<string, array<int, string>>>> $entries
     */
    private static function isValid(mixed $entries): bool
    {
        if (!\is_array($entries)) {
            return false;
        }

        foreach ($entries as $test => $paths) {
            if (!\is_string($test) || !\is_array($paths) || !self::isValidPaths($paths)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<mixed> $paths
     */
    private static function isValidPaths(array $paths): bool
    {
        foreach ($paths as $path => $rules) {
            if (!\is_string($path) || !\is_array($rules)) {
                return false;
            }

            foreach ($rules as $rule => $identifiers) {
                if (
                    !\is_string($rule)
                    || !\is_array($identifiers)
                    || !array_is_list($identifiers)
                    || array_filter($identifiers, \is_string(...)) !== $identifiers
                ) {
                    return false;
                }
            }
        }

        return true;
    }
}
