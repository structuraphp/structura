<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Generator;
use InvalidArgumentException;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\ExprType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use Symfony\Component\Finder\Finder;

final class ExecuteService
{
    private AssertBuilder $builder;

    public function __construct(private readonly RuleValuesObject $ruleValuesObject) {}

    public function assert(): AssertBuilder
    {
        $service = new ParseService(
            $this->ruleValuesObject->getDescriptorType(),
        );
        $this->builder = new AssertBuilder();

        if ($this->ruleValuesObject->finder instanceof Finder) {
            $description = $service->parse($this->ruleValuesObject->finder);
        } elseif ($this->ruleValuesObject->raw !== '') {
            $description = $service->parseRaw($this->ruleValuesObject->raw);
        } else {
            throw new InvalidArgumentException();
        }

        $this->execute($description, $this->ruleValuesObject->should);

        return $this->builder;
    }

    /**
     * @param Generator<ClassDescription|ScriptDescription> $descriptions
     */
    private function execute(Generator $descriptions, AbstractExpr $assertions): void
    {
        /** @var AbstractExpr|ExprInterface $assert */
        foreach ($assertions as $assert) {
            $this->builder->addPass((string) $assert);
        }

        /** @var ScriptDescription $description */
        foreach ($descriptions as $description) {
            if ($this->executeThat($description)) {
                continue;
            }

            $this->executeShould($assertions, $description);
        }
    }

    private function executeThat(ClassDescription|ScriptDescription $description): bool
    {
        if (!$this->ruleValuesObject->that instanceof Expr) {
            return false;
        }

        /** @var AbstractExpr|ExprInterface $assert */
        foreach ($this->ruleValuesObject->that as $assert) {
            $predicate = $this->predicate($assert, $description);

            if (!$predicate) {
                return true;
            }
        }

        return false;
    }

    private function executeShould(
        AbstractExpr $assertions,
        ClassDescription|ScriptDescription $description,
    ): void {
        /** @var AbstractExpr|ExprInterface $assert */
        foreach ($assertions as $assert) {
            $predicate = $this->predicate($assert, $description);

            $isExcept = $this
                ->ruleValuesObject
                ->except
                ?->isExcept($description->namespace, $assert::class);

            if ($isExcept === true) {
                if (!$predicate) {
                    $this->builder->addExcept($description->namespace, (string) $assert);

                    continue;
                }

                $this->builder->addWarning($description->namespace, (string) $assert);
            }

            if (!$predicate) {
                $this->builder->addViolation((string) $assert, $assert, $description);
            }
        }
    }

    private function assertGroup(
        AbstractExpr $assertions,
        ClassDescription|ScriptDescription $description,
    ): bool {
        $isPass = true;

        /** @var AbstractExpr|ExprInterface $assert */
        foreach ($assertions as $key => $assert) {
            $predicate = $this->predicate($assert, $description);

            $isExcept = $this
                ->ruleValuesObject
                ->except
                ?->isExcept($description->namespace, $assert::class);

            if ($isExcept === true) {
                if (!$predicate) {
                    $this->builder->addExcept($description->namespace, (string) $assert);

                    continue;
                }

                $this->builder->addWarning($description->namespace, (string) $assert);
            }

            if ($key === 0) {
                $isPass = $predicate;
            }

            if ($assertions->getExprType() === ExprType::And) {
                $isPass = $isPass && $predicate;

                continue;
            }

            $isPass = $isPass || $predicate;
            if ($isPass) {
                return true;
            }
        }

        return $isPass;
    }

    private function predicate(
        AbstractExpr|ExprInterface $assert,
        ClassDescription|ScriptDescription $description,
    ): bool {
        if ($assert instanceof ExprScriptInterface) {
            return $assert->assert($description);
        }

        if ($assert instanceof ExprInterface && $description instanceof ClassDescription) {
            return $assert->assert($description);
        }

        if ($assert instanceof AbstractExpr) {
            return $this->assertGroup($assert, $description);
        }

        throw new InvalidArgumentException();
    }
}
