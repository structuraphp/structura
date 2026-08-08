<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Generator;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Contracts\PathResolverAwareInterface;
use StructuraPhp\Structura\Enums\ExprType;
use StructuraPhp\Structura\Events\ExceptEvent;
use StructuraPhp\Structura\Events\NoticeEvent;
use StructuraPhp\Structura\Events\PassEvent;
use StructuraPhp\Structura\Events\ViolationEvent;
use StructuraPhp\Structura\Events\WarningEvent;
use StructuraPhp\Structura\Exception\Console\EventException;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Symfony\Component\Finder\Finder;

final class ExecuteService
{
    private ParseService $parseService;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RuleValuesObject $ruleValuesObject,
        private readonly ?SourceTestValueObject $sourceTest = null,
    ) {
        $this->parseService = new ParseService(
            $this->ruleValuesObject->getDescriptorType(),
        );
    }

    public function assert(): void
    {
        $description = $this->ruleValuesObject->finder instanceof Finder
            ? $this->parseService->parse($this->ruleValuesObject->finder)
            : $this->parseRawFiles();

        if ($this->isEmptySource()) {
            $message = sprintf(
                'No PHP files found for test "<promote>%s</promote>". Assertions were skipped.',
                $this->sourceTest->testClassname ?? '',
            );

            $this->dispatcher->dispatch(new NoticeEvent(
                key: $message,
                message: $message,
            ));
        }

        $this->execute($description, $this->ruleValuesObject->should);
    }

    private function isEmptySource(): bool
    {
        return $this->ruleValuesObject->finder instanceof Finder
            && $this->ruleValuesObject->finder->count() === 0;
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
            $this->dispatcher->dispatch(new PassEvent((string) $assert));
        }

        $this->injectPathResolvers($assertions);

        try {
            /** @var ScriptDescription $description */
            foreach ($descriptions as $description) {
                if ($this->executeThat($description)) {
                    continue;
                }

                $this->executeShould($assertions, $description);
            }
        } catch (EventException $eventException) {
            $this->dispatcher->dispatch($eventException->event);
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
                ?->isExcept($description, $assert::class);

            if ($isExcept === true) {
                if (!$predicate) {
                    $this->dispatcher->dispatch(new ExceptEvent(
                        key: $description->getResourceName(),
                        message: (string) $assert,
                    ));

                    continue;
                }

                $this->dispatcher->dispatch(new WarningEvent(
                    key: (string) $assert,
                    message: sprintf(
                        '<promote>%s</promote> exception for <promote>%s</promote> is no longer applicable',
                        $assert::class,
                        $description->getResourceName(),
                    ),
                    isAssertionWarning: true,
                ));
            }

            if (!$predicate) {
                $this->dispatcher->dispatch(new ViolationEvent(
                    key: (string) $assert,
                    violations: $this->computeViolations($assert, $description),
                ));
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
                ?->isExcept($description, $assert::class);

            if ($isExcept === true) {
                if (!$predicate) {
                    $this->dispatcher->dispatch(new ExceptEvent(
                        key: $description->getResourceName(),
                        message: (string) $assert,
                    ));

                    continue;
                }

                $this->dispatcher->dispatch(new WarningEvent(
                    key: (string) $assert,
                    message: sprintf(
                        '<promote>%s</promote> exception for <promote>%s</promote> is no longer applicable',
                        $assert::class,
                        $description->getResourceName(),
                    ),
                    isAssertionWarning: true,
                ));
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

    /**
     * @return array<int, ViolationValueObject>
     */
    private function computeViolations(
        AbstractExpr|ExprInterface $assert,
        ClassDescription|ScriptDescription $description,
    ): array {
        if ($assert instanceof ExprScriptInterface) {
            return $assert->getViolation($description);
        }

        if ($assert instanceof ExprInterface && $description instanceof ClassDescription) {
            return $assert->getViolation($description);
        }

        if ($assert instanceof AbstractExpr) {
            return $assert->getViolations($description);
        }

        throw new InvalidArgumentException();
    }

    /**
     * Injects path resolvers into all PathResolverAwareInterface assertions,
     * recursively descending into nested AbstractExpr groups (or/and).
     */
    private function injectPathResolvers(AbstractExpr $assertions): void
    {
        if ($this->ruleValuesObject->pathResolvers === []) {
            return;
        }

        /** @var AbstractExpr|ExprInterface $assert */
        foreach ($assertions as $assert) {
            if ($assert instanceof PathResolverAwareInterface) {
                $assert->setPathResolvers($this->ruleValuesObject->pathResolvers);
            }

            if ($assert instanceof AbstractExpr) {
                $this->injectPathResolvers($assert);
            }
        }
    }
}
