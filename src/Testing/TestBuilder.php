<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Testing;

use StructuraPhp\Structura\Builder\AllClasses;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;

abstract class TestBuilder
{
    /** @var array<int,AllClasses<Expr>|AllClasses<ExprScript>> */
    private array $rules = [];

    /**
     * @return AllClasses<Expr>
     */
    final public function allClasses(): AllClasses
    {
        $rule = AllClasses::allClasses();
        $this->rules[] = $rule;

        return $rule;
    }

    /**
     * @return AllClasses<ExprScript>
     */
    final public function allScripts(): AllClasses
    {
        $rule = AllClasses::allScripts();
        $this->rules[] = $rule;

        return $rule;
    }

    /**
     * @return array<int,AllClasses<Expr>|AllClasses<ExprScript>>
     */
    final public function getRules(): array
    {
        $rules = $this->rules;
        $this->rules = [];

        return $rules;
    }
}
