<?php

declare(strict_types=1);

namespace Structura;

use Structura\Contracts\ExprInterface;

class Except
{
    /** @var array<class-string, array<int, class-string<ExprInterface>>>  */
    private array $expects;

    /**
     * @param class-string $className
     * @param class-string<ExprInterface> $expr
     */
    public function byRule(string $className, string $expr): self
    {
        $this->expects[$className][] = $expr;

        return $this;
    }

    /**
     * @param string|null $className if anonymous class then null
     * @param class-string<ExprInterface|Expr> $expr
     */
    public function isExcept(?string $className, string $expr): bool
    {
        return $className !== null
            && isset($this->expects[$className])
            && \in_array($expr, $this->expects[$className], true);
    }
}
