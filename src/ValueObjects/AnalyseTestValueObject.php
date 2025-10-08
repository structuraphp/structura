<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

class AnalyseTestValueObject
{
    public function __construct(
        public string $textDox,
        public string $classname,
        public RuleValuesObject $ruleValueObject,
        public AssertValueObject $assertValueObject,
    ) {}
}
