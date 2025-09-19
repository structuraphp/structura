<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Testing;

use StructuraPhp\Structura\Builder\AllClasses;
use StructuraPhp\Structura\Expr;

abstract class TestBuilder
{
    /** @var array<int,AllClasses<Expr>> */
    private array $rules = [];

    /**
     * @return AllClasses<Expr>
     */
    public function allClasses(): AllClasses
    {
        $this->rules[] = AllClasses::allClasses();

        return $this->rules[array_key_last($this->rules)];
    }

    /**
     * @return array<int,AllClasses<Expr>>
     */
    public function getRules(): array
    {
        $rules = $this->rules;
        $this->rules = [];

        return $rules;
    }
}
