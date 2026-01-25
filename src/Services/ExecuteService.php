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
use StructuraPhp\Structura\Exception\Console\NoticeException;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use Symfony\Component\Finder\Finder;

final class ExecuteService
{
    private AssertBuilder $builder;

    private ParseService $parseService;

    public function __construct(private readonly RuleValuesObject $ruleValuesObject)
    {
        $this->parseService = new ParseService(
            $this->ruleValuesObject->getDescriptorType(),
        );
        $this->builder = new AssertBuilder();
    }

    public function assert(): AssertBuilder
    {
        $description = $this->ruleValuesObject->finder instanceof Finder
            ? $this->parseService->parse($this->ruleValuesObject->finder)
            : $this->parseRawFiles();

        $this->execute($description, $this->ruleValuesObject->should);

        return $this->builder;
    }

    /**
     * @return Generator<ClassDescription|ScriptDescription>
     */
    private function parseRawFiles(): Generator
    {
        foreach ($this->ruleValuesObject->raws as $path => $raw) {
            yield from $this->parseService->parseRaw($raw, $path);
        }
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

        try {
            /** @var ScriptDescription $description */
            foreach ($descriptions as $description) {
                if ($this->executeThat($description)) {
                    continue;
                }

                $this->executeShould($assertions, $description);
            }
        } catch (NoticeException $noticeException) {
            $this->builder->addNotice($noticeException->getMessage(), $noticeException->getMessage());
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

                $this->builder->addWarning((string) $assert, $assert, $description);
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

                $this->builder->addWarning((string) $assert, $assert, $description);
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
