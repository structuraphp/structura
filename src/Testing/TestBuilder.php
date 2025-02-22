<?php

declare(strict_types=1);

namespace Structura\Testing;

use Structura\Builder\AllClasses;

abstract class TestBuilder
{
    /** @var array<int,AllClasses> */
    private array $rules = [];

    public function allClasses(): AllClasses
    {
        $this->rules[] = new AllClasses();

        return $this->rules[array_key_last($this->rules)];
    }

    /**
     * @return array<int,AllClasses>
     */
    public function getRules(): array
    {
        $rules = $this->rules;
        $this->rules = [];

        return $rules;
    }
}
