<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

class AnalyseTestValueObject
{
    public function __construct(
        public SourceTestValueObject $source,
        public RuleValuesObject $ruleValueObject,
        public AssertValueObject $assertValueObject,
    ) {}
}
