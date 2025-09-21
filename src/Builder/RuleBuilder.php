<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use Symfony\Component\Finder\Finder;

class RuleBuilder
{
    /** @var array<string, string> */
    public array $raws = [];

    public ?Finder $finder = null;

    public ?AbstractExpr $that = null;

    public AbstractExpr $should;

    public ?Except $except = null;

    public function addRaw(string $raw, string $pathname): self
    {
        if ($pathname === '') {
            $nb = count($this->raws);
            $pathname = 'tmp/run_' . $nb . '.php';
        }

        $this->raws[$pathname] = $raw;

        return $this;
    }

    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function setThat(AbstractExpr $that): self
    {
        $this->that = $that;

        return $this;
    }

    public function setShould(AbstractExpr $should): self
    {
        $this->should = $should;

        return $this;
    }

    public function setExpect(Except $expect): self
    {
        $this->except = $expect;

        return $this;
    }

    public function getRuleObject(): RuleValuesObject
    {
        return new RuleValuesObject(
            raws: $this->raws,
            finder: $this->finder,
            that: $this->that,
            except: $this->except,
            should: $this->should,
        );
    }
}
