<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Contracts\FinderInterface;
use StructuraPhp\Structura\Contracts\ThatInterface;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use Symfony\Component\Finder\Finder;

/**
 * @template T of AbstractExpr
 *
 * @implements FinderInterface<T>
 */
readonly class AllClasses implements FinderInterface
{
    private RuleBuilder $ruleBuilder;

    /**
     * @param class-string<T> $abstractExpr
     */
    final private function __construct(
        private string $abstractExpr,
    ) {
        $this->ruleBuilder = new RuleBuilder();
    }

    /**
     * @return AllClasses<Expr>
     */
    public static function allClasses(): self
    {
        return new self(Expr::class);
    }

    /**
     * @return AllClasses<ExprScript>
     */
    public static function allScripts(): self
    {
        return new self(ExprScript::class);
    }

    /**
     * @param array<int,string>|string $dirs
     * @param null|Closure(Finder): ?Finder $closure
     *
     * @return ThatInterface<T>
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

        return new FinderBuilder($this->ruleBuilder, $this->abstractExpr);
    }

    /**
     * @return ThatInterface<T>
     */
    public function fromRaw(string $raw): ThatInterface
    {
        $this->ruleBuilder->addRaw($raw);

        return new FinderBuilder($this->ruleBuilder, $this->abstractExpr);
    }

    public function getRuleBuilder(): RuleBuilder
    {
        return $this->ruleBuilder;
    }
}
