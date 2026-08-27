<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final class AnalyseTestValueObject
{
    /**
     * @param array<int, RuleDescriptionValueObject> $ruleDescriptions
     */
    public function __construct(
        public SourceTestValueObject $source,
        public array $ruleDescriptions,
        public AssertValueObject $assertValueObject,
    ) {}
}
