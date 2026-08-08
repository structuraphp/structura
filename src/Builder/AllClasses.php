<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use Closure;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Contracts\FinderInterface;
use StructuraPhp\Structura\Contracts\ThatInterface;
use StructuraPhp\Structura\Events\NoticeEvent;
use StructuraPhp\Structura\Exception\Console\EventException;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
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
     * @param null|(Closure(Finder): (Finder|void)) $closure
     *
     * @return ThatInterface<T>
     */
    public function fromDir(array|string $dirs, ?Closure $closure = null): ThatInterface
    {
        try {
            $finder = Finder::create()
                ->files()
                ->followLinks()
                ->sortByName()
                ->name('*.php')
                ->in($dirs);
        } catch (DirectoryNotFoundException $directoryNotFoundException) {
            $dirLabel = is_array($dirs) ? implode(', ', $dirs) : $dirs;

            throw new EventException(
                new NoticeEvent(
                    key: $dirLabel,
                    message: sprintf('Directory not found: "%s". Assertions were skipped.', $dirLabel),
                ),
                $directoryNotFoundException->getCode(),
                $directoryNotFoundException,
            );
        }

        if ($closure instanceof Closure) {
            $closure($finder);
        }

        $this->ruleBuilder->setFinder($finder);

        return new FinderBuilder($this->ruleBuilder, $this->abstractExpr);
    }

    /**
     * @return ThatInterface<T>
     */
    public function fromRaw(string $raw, string $pathname = ''): ThatInterface
    {
        $this->ruleBuilder->addRaw($raw, $pathname);

        return new FinderBuilder($this->ruleBuilder, $this->abstractExpr);
    }

    /**
     * @param array<array-key,string> $raws
     *
     * @return ThatInterface<T>
     */
    public function fromRawMultiple(array $raws): ThatInterface
    {
        foreach ($raws as $pathname => $raw) {
            $this
                ->ruleBuilder
                ->addRaw($raw, is_string($pathname) ? $pathname : '');
        }

        return new FinderBuilder($this->ruleBuilder, $this->abstractExpr);
    }

    public function getRuleBuilder(): RuleBuilder
    {
        return $this->ruleBuilder;
    }
}
