<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use InvalidArgumentException;
use ParseError;
use StructuraPhp\Structura\ValueObjects\BaselineEntryValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

/**
 * Tolerates the violations recorded in a baseline file and reports the
 * entries that no longer match any violation.
 */
final class BaselineService
{
    /** @var array<string, array<string, array<string, true>>> path => rule => identifier */
    private array $entries = [];

    /** @var array<string, array<string, array<string, true>>> path => rule => identifier */
    private array $used = [];

    private int $countTolerated = 0;

    /**
     * @param array<string, array<string, array<int, string>>> $entries path => rule => identifiers
     */
    public function __construct(
        array $entries,
        private readonly string $baseDirectory,
    ) {
        foreach ($entries as $path => $rules) {
            foreach ($rules as $rule => $identifiers) {
                foreach ($identifiers as $identifier) {
                    $this->entries[$path][$rule][$identifier] = true;
                }
            }
        }
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
                \sprintf('Baseline file "%s" must return array<string, array<class-string, list<string>>>', $path),
            );
        }

        return new self($entries, $baseDirectory);
    }

    public function isTolerated(ViolationValueObject $violation): bool
    {
        $entry = BaselineEntryValueObject::fromViolation($violation, $this->baseDirectory);
        if (
            !$entry instanceof BaselineEntryValueObject
            || !isset($this->entries[$entry->path][$entry->rule][$entry->identifier])
        ) {
            return false;
        }

        $this->used[$entry->path][$entry->rule][$entry->identifier] = true;
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
        $warnings = [];
        foreach ($this->entries as $path => $rules) {
            foreach ($rules as $rule => $identifiers) {
                foreach (array_keys($identifiers) as $identifier) {
                    if (isset($this->used[$path][$rule][$identifier])) {
                        continue;
                    }

                    $warnings[] = \sprintf(
                        'Baseline entry <promote>%s</promote> for <promote>%s</promote> in <promote>%s</promote> no longer matches any violation',
                        $identifier,
                        $rule,
                        $path,
                    );
                }
            }
        }

        sort($warnings);

        return $warnings;
    }

    /**
     * @phpstan-assert-if-true array<string, array<string, array<int, string>>> $entries
     */
    private static function isValid(mixed $entries): bool
    {
        if (!\is_array($entries)) {
            return false;
        }

        foreach ($entries as $path => $rules) {
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
