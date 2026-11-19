<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use StructuraPhp\Structura\Console\Enums\StyleCustom;
use Symfony\Component\Filesystem\Path;

/**
 * Baseline key of a violation, per test: never its line, custom message or rule configuration.
 */
final readonly class BaselineEntryValueObject
{
    public function __construct(
        public string $test,
        public string $path,
        public string $rule,
        public string $identifier,
    ) {}

    /**
     * No part contains a NUL byte: anonymous rules, whose names do, are never baselined.
     */
    public function key(): string
    {
        return implode("\0", [$this->test, $this->path, $this->rule, $this->identifier]);
    }

    /**
     * Null for what cannot match across runs: a fromRaw source (no real file)
     * and an anonymous rule (its name holds a NUL byte and an absolute path).
     */
    public static function fromViolation(
        SourceTestValueObject $source,
        ViolationValueObject $violation,
        string $baseDirectory,
    ): ?self {
        $pathname = $violation->pathname;
        if (
            $pathname === null
            || !Path::isAbsolute($pathname)
            || str_contains($violation->assertClassname, '@anonymous')
        ) {
            return null;
        }

        return new self(
            $source->testClassname . '::' . $source->methodName,
            Path::makeRelative($pathname, $baseDirectory),
            // short name: the namespace only adds noise to the file
            basename(strtr($violation->assertClassname, '\\', '/')),
            self::identifier($violation->messageViolation, $baseDirectory),
        );
    }

    /**
     * The <fire> element, free of rule configuration, otherwise the untagged message.
     */
    private static function identifier(string $message, string $baseDirectory): string
    {
        $identifier = preg_match_all('/<fire>(.*?)<\/fire>/', $message, $matches) > 0
            ? implode(', ', $matches[1])
            : self::withoutStyleTags($message);

        // paths in messages (includes, corresponding files) must not depend on the checkout directory
        return str_replace($baseDirectory . '/', '', $identifier);
    }

    /**
     * strip_tags() would also remove generic types such as array<string, int>.
     */
    private static function withoutStyleTags(string $message): string
    {
        $tags = implode('|', array_column(StyleCustom::cases(), 'value'));

        return (string) preg_replace('/<\/?(' . $tags . ')>/', '', $message);
    }
}
