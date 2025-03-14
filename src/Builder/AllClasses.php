<?php

declare(strict_types=1);

namespace Structura\Builder;

use Closure;
use Structura\Contracts\FinderInterface;
use Structura\Contracts\ThatInterface;
use Symfony\Component\Finder\Finder;

class AllClasses implements FinderInterface
{
    private readonly RuleBuilder $ruleBuilder;

    public function __construct()
    {
        $this->ruleBuilder = new RuleBuilder();
    }

    /**
     * @param array<int,string>|string $dirs
     * @param Closure(Finder): ?Finder|null $closure
     */
    public function fromDir(array|string $dirs, ?Closure $closure = null): ThatInterface
    {
        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->sortByName()
            ->name('*.php')
            ->in($dirs);

        if ($closure instanceof Closure) {
            $closure($finder);
        }

        $this->ruleBuilder->addFinder($finder);

        return new FinderBuilder($this->ruleBuilder);
    }

    public function fromRaw(string $raw): ThatInterface
    {
        $this->ruleBuilder->addRaw($raw);

        return new FinderBuilder($this->ruleBuilder);
    }

    public function getRuleBuilder(): RuleBuilder
    {
        return $this->ruleBuilder;
    }
}
