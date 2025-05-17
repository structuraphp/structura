<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Generator;
use InvalidArgumentException;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ExprType;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use Symfony\Component\Finder\Finder;

final class ExecuteService
{
    private AssertBuilder $builder;

    public function __construct(private readonly RuleValuesObject $ruleValuesObject) {}

    public function assert(): AssertBuilder
    {
        $service = new ParseService();
        $this->builder = new AssertBuilder();

        if ($this->ruleValuesObject->finder instanceof Finder) {
            $classes = $service->parse($this->ruleValuesObject->finder);
        } elseif ($this->ruleValuesObject->raw !== '') {
            $classes = $service->parseRaw($this->ruleValuesObject->raw);
        } else {
            throw new InvalidArgumentException();
        }

        $this->execute($classes, $this->ruleValuesObject->should);

        return $this->builder;
    }

    /**
     * @param Generator<ClassDescription> $classes
     */
    private function execute(Generator $classes, Expr $assertions): void
    {
        /** @var Expr|ExprInterface $assert */
        foreach ($assertions as $assert) {
            $this->builder->addPass((string) $assert);
        }

        /** @var ClassDescription $class */
        foreach ($classes as $class) {
            if ($this->executeThat($class)) {
                continue;
            }

            $this->executeShould($assertions, $class);
        }
    }

    private function executeThat(ClassDescription $class): bool
    {
        if (!$this->ruleValuesObject->that instanceof Expr) {
            return false;
        }

        foreach ($this->ruleValuesObject->that as $expression) {
            $predicate = $expression instanceof ExprInterface
                ? $expression->assert($class)
                : $this->assertGroup($expression, $class);

            if (!$predicate) {
                return true;
            }
        }

        return false;
    }

    private function executeShould(
        Expr $assertions,
        ClassDescription $class,
    ): void {
        /** @var Expr|ExprInterface $assert */
        foreach ($assertions as $assert) {
            if ($assert instanceof ExprInterface) {
                $predicate = $assert->assert($class);
            } else {
                $predicate = $this->assertGroup($assert, $class);
            }

            if ($this->ruleValuesObject->except?->isExcept($class->namespace, $assert::class)) {
                if (!$predicate) {
                    $this->builder->addExcept($class->namespace, (string) $assert);

                    continue;
                }

                $this->builder->addWarning($class->namespace, (string) $assert);
            }

            if (!$predicate) {
                $this->builder->addViolation((string) $assert, $assert, $class);
            }
        }
    }

    private function assertGroup(
        Expr $assertions,
        ClassDescription $class,
    ): bool {
        $isPass = true;

        /** @var Expr|ExprInterface $assert */
        foreach ($assertions as $key => $assert) {
            if ($assert instanceof ExprInterface) {
                $predicate = $assert->assert($class);
            } else {
                $predicate = $this->assertGroup($assert, $class);
            }

            if ($this->ruleValuesObject->except?->isExcept($class->namespace, $assert::class)) {
                if (!$predicate) {
                    $this->builder->addExcept($class->namespace, (string) $assert);

                    continue;
                }

                $this->builder->addWarning($class->namespace, (string) $assert);
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
}
