<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use StructuraPhp\Structura\Contracts\ExprInterface;

class Except
{
    /** @var array<class-string, array<int, class-string<ExprInterface>>> */
    private array $expects;

    /**
     * @param array<int, class-string>|class-string $className
     * @param class-string<ExprInterface> $expression
     */
    public function byClassname(
        array|string $className,
        string $expression,
    ): self {
        $classNames = \is_array($className) ? $className : [$className];

        foreach ($classNames as $class) {
            $this->expects[$class][] = $expression;
        }

        return $this;
    }

    /**
     * @param null|string $className if anonymous class then null
     * @param class-string<Expr|ExprInterface> $expr
     */
    public function isExcept(?string $className, string $expr): bool
    {
        return $className !== null
            && isset($this->expects[$className])
            && \in_array($expr, $this->expects[$className], true);
    }
}
