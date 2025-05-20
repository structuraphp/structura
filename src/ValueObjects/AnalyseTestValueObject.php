<?php

namespace StructuraPhp\Structura\ValueObjects;

use StructuraPhp\Structura\Builder\AssertBuilder;

class AnalyseTestValueObject
{
    public function __construct(
        public string $textDox,
        public string $classname,
        public RuleValuesObject $ruleValueObject,
        public AssertBuilder $assertBuilder,
    ){

    }
}