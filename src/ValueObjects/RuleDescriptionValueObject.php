<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

/**
 * Serializable projection of a RuleValuesObject.
 *
 * A RuleValuesObject carries a Symfony Finder and an AbstractExpr tree, both of which may
 * hold closures and therefore cannot cross a process boundary. Progress formatters only ever
 * need the source count, the source kind and the stringified "that" expressions, so that
 * subset is projected here and is what travels between the workers and the parent process.
 */
final readonly class RuleDescriptionValueObject
{
    /**
     * @param null|array<int, string> $thatExpressions null when the rule declares no that() clause,
     *                                                 an empty array when it declares an empty one
     */
    public function __construct(
        public int $sourceCount,
        public bool $fromFinder,
        public ?array $thatExpressions = null,
    ) {}
}
