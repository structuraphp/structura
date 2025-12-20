<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use InvalidArgumentException;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;

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
        AbstractExpr|ExprInterface $assert,
        ClassDescription|ScriptDescription $description,
    ): self {
        $this->pass[$key] = 0;
        if ($assert instanceof ExprScriptInterface) {
            $this->violations[$key][] = $assert->getViolation($description);

            return $this;
        }

        if ($assert instanceof ExprInterface && $description instanceof ClassDescription) {
            $this->violations[$key][] = $assert->getViolation($description);

            return $this;
        }

        if ($assert instanceof AbstractExpr) {
            $this->violations[$key] = array_merge(
                $this->violations[$key] ?? [],
                $assert->getViolations($description),
            );

            return $this;
        }

        throw new InvalidArgumentException();
    }

    public function addWarning(
        string $key,
        AbstractExpr|ExprInterface $assert,
        ClassDescription|ScriptDescription $description,
    ): self {
        $this->pass[$key] = 2;
        $classname = $description->namespace;

        if (\is_string($classname)) {
            $this->warnings[$key][] = sprintf(
                'Except <promote>%s</promote> for <promote>%s</promote> is not applicable',
                $assert::class,
                $classname,
            );
        }

        return $this;
    }

    public function getAssertValueObject(): AssertValueObject
    {
        return new AssertValueObject(
            $this->pass,
            $this->violations,
            $this->exceptions,
            $this->warnings,
        );
    }
}
