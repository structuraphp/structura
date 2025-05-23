<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

/**
 * @phpstan-import-type ViolationsByTest from \StructuraPhp\Structura\ValueObjects\AnalyseValueObject
 */
class AssertBuilder
{
    /** @var array<string,int> */
    private array $pass = [];

    /** @var ViolationsByTest */
    private array $violations = [];

    /** @var array<string, array<int, string>> */
    private array $exceptions = [];

    /** @var array<string, array<int, string>> */
    private array $warnings = [];

    public function addExcept(?string $classname, string $expr): self
    {
        if (\is_string($classname)) {
            $this->exceptions[$classname][] = $expr;
        }

        return $this;
    }

    public function addPass(string $key): self
    {
        $this->pass[$key] = 1;

        return $this;
    }

    public function addViolation(
        string $key,
        Expr|ExprInterface $assert,
        ClassDescription $class,
    ): self {
        $this->pass[$key] = 0;
        if ($assert instanceof ExprInterface) {
            $this->violations[$key][] = $assert->getViolation($class);
        } else {
            $this->violations[$key] = $assert->getViolations($class);
        }

        return $this;
    }

    public function addWarning(?string $classname, string $key): self
    {
        $this->pass[$key] = 2;
        if (\is_string($classname)) {
            $this->warnings[$key][] = $classname;
        }

        return $this;
    }

    public function countViolation(string $key): int
    {
        return \count($this->violations[$key] ?? []);
    }

    public function countWarning(string $key): int
    {
        return \count($this->warnings[$key] ?? []);
    }

    public function countAssertsSuccess(): int
    {
        return array_count_values($this->pass)[1] ?? 0;
    }

    public function countAssertsFailure(): int
    {
        return array_count_values($this->pass)[0] ?? 0;
    }

    public function countAssertsWarning(): int
    {
        return array_count_values($this->pass)[2] ?? 0;
    }

    /**
     * @return array<string,int>
     */
    public function getPass(): array
    {
        return $this->pass;
    }

    /**
     * @return ViolationsByTest
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
