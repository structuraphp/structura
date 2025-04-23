<?php

declare(strict_types=1);

namespace Structura\Services;

use Generator;
use InvalidArgumentException;
use Structura\Builder\AssertBuilder;
use Structura\Contracts\ExprInterface;
use Structura\Enums\ExprType;
use Structura\Expr;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\RuleValuesObject;
use Symfony\Component\Finder\Finder;

class ExecuteService
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

        $this->execute($classes, $this->ruleValuesObject->shoulds);

        return $this->builder;
    }

    /**
     * @param Generator<ClassDescription> $classes
     */
    protected function execute(Generator $classes, Expr $assertions): void
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
        if (!$this->ruleValuesObject->thats instanceof Expr) {
            return false;
        }

        foreach ($this->ruleValuesObject->thats as $expression) {
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

            if ($this->ruleValuesObject->except?->isExcept($class->namespace, $assert::class) && !$predicate) {
                $this->builder->addExcept($class->namespace, (string) $assert);
                $predicate = true;
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

            if ($this->ruleValuesObject->except?->isExcept($class->namespace, $assert::class) && !$predicate) {
                $this->builder->addExcept($class->namespace, (string) $assert);
                $predicate = true;
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
