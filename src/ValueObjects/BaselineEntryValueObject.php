<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use StructuraPhp\Structura\Console\Enums\StyleCustom;
use Symfony\Component\Filesystem\Path;

/**
 * Identifies a violation in a baseline, independently of its line,
 * its custom message and the configuration of its rule.
 * The test is part of the key: a violation recorded for one test never tolerates
 * the same violation reported by another test.
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
     * Unique among entries: no part contains a NUL byte (anonymous rules are never baselined).
     */
    public function key(): string
    {
        return implode("\0", [$this->test, $this->path, $this->rule, $this->identifier]);
    }

    /**
     * Returns null for what can never match across runs, and is never baselined:
     * a source without a real file (fromRaw) and a rule written as an anonymous class,
     * whose name holds a NUL byte and the absolute path of its file.
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
            // the short name of the rule: its namespace only makes the file harder to read
            basename(strtr($violation->assertClassname, '\\', '/')),
            self::identifier($violation->messageViolation, $baseDirectory),
        );
    }

    /**
     * The faulty element (<fire> tags) when the message names one: it does not
     * contain the rule configuration. Otherwise the whole message without tags.
     */
    private static function identifier(string $message, string $baseDirectory): string
    {
        $identifier = preg_match_all('/<fire>(.*?)<\/fire>/', $message, $matches) > 0
            ? implode(', ', $matches[1])
            : self::withoutStyleTags($message);

        // paths in a message (includes, corresponding files) must not depend on the checkout directory
        return str_replace($baseDirectory . '/', '', $identifier);
    }

    /**
     * Only the console style tags: strip_tags() would also eat generic types such as array<string, int>.
     */
    private static function withoutStyleTags(string $message): string
    {
        $tags = implode('|', array_column(StyleCustom::cases(), 'value'));

        return (string) preg_replace('/<\/?(' . $tags . ')>/', '', $message);
    }
}
