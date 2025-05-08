<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use Symfony\Component\Finder\Finder;

class RuleBuilder
{
    public string $raw = '';

    public ?Finder $finder = null;

    public ?Expr $thats = null;

    public Expr $shoulds;

    public ?Except $except = null;

    public function addRaw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }

    public function addFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function addThat(Expr $that): self
    {
        $this->thats = $that;

        return $this;
    }

    public function addShould(Expr $should): self
    {
        $this->shoulds = $should;

        return $this;
    }

    public function addExpect(Except $expect): self
    {
        $this->except = $expect;

        return $this;
    }

    public function getRuleObject(): RuleValuesObject
    {
        return new RuleValuesObject(
            raw: $this->raw,
            finder: $this->finder,
            thats: $this->thats,
            except: $this->except,
            shoulds: $this->shoulds,
        );
    }
}
