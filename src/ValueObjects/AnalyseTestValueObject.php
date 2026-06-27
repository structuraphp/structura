<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

final class AnalyseTestValueObject
{
    /**
     * @param array<int, RuleValuesObject> $ruleValueObjects
     */
    public function __construct(
        public SourceTestValueObject $source,
        public array $ruleValueObjects,
        public AssertValueObject $assertValueObject,
    ) {}
}
