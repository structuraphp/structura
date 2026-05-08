<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Contracts\PathResolverAwareInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final class ToUseInclude implements ExprScriptInterface, PathResolverAwareInterface
{
    /** @var array<string, string> */
    private array $pathResolvers = [];

    public function __construct(
        private IncludeType $includeType,
        private ?string $pathPattern = null,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        if ($this->pathPattern !== null) {
            return \sprintf(
                'to use <promote>%s</promote> with path matching <promote>%s</promote>',
                $this->includeType->label(),
                $this->pathPattern,
            );
        }

        return \sprintf('to use <promote>%s</promote>', $this->includeType->label());
    }

    public function setPathResolvers(array $pathResolvers): void
    {
        $this->pathResolvers = $pathResolvers;
    }

    public function assert(ScriptDescription $description): bool
    {
        foreach ($description->includes as $include) {
            if ($include->type !== $this->includeType->value) {
                return false;
            }

            if ($this->pathPattern !== null) {
                $path = $this->resolvePath($include->expr, $description->getFileBasename());

                if ($path === null || !fnmatch($this->pathPattern, $path)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $results = [];

        foreach ($description->includes as $include) {
            if ($include->type !== $this->includeType->value) {
                $results[] = new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> must use <promote>%s</promote> but uses <fire>%s</fire>',
                        $description->getResourceName(),
                        $this->includeType->label(),
                        IncludeType::from($include->type)->label(),
                    ),
                    $this::class,
                    $include->getLine(),
                    $description->getFileBasename(),
                    $this->message,
                );

                continue;
            }

            if (!is_string($this->pathPattern)) {
                continue;
            }

            $path = $this->resolvePath($include->expr, $description->getFileBasename());

            if ($path === null) {
                $results[] = new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> uses a dynamic path which cannot be verified against pattern <fire>%s</fire>',
                        $description->getResourceName(),
                        $this->pathPattern,
                    ),
                    $this::class,
                    $include->getLine(),
                    $description->getFileBasename(),
                    $this->message,
                );

                continue;
            }

            if (!fnmatch($this->pathPattern, $path)) {
                $results[] = new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> uses path <fire>%s</fire> which does not match pattern <promote>%s</promote>',
                        $description->getResourceName(),
                        $path,
                        $this->pathPattern,
                    ),
                    $this::class,
                    $include->getLine(),
                    $description->getFileBasename(),
                    $this->message,
                );
            }
        }

        return $results;
    }

    private function resolvePath(Expr $expr, string $filePath): ?string
    {
        $self = $this;

        $evaluator = null;
        $evaluator = new ConstExprEvaluator(static function (Expr $node) use ($filePath, $self, &$evaluator): string {
            if ($node instanceof Dir) {
                return dirname($filePath);
            }

            if ($node instanceof File) {
                return $filePath;
            }

            if ($node instanceof FuncCall && $node->name instanceof Name) {
                $funcName = $node->name->toString();

                if (isset($self->pathResolvers[$funcName])) {
                    return $self->pathResolvers[$funcName];
                }

                if ($funcName === 'dirname') {
                    $arg = $node->args[0] instanceof Arg
                        ? $node->args[0]->value
                        : null;

                    if ($arg instanceof Expr) {
                        $resolved = $self->resolvePath($arg, $filePath);

                        if ($resolved !== null) {
                            $levels = 1;

                            if (isset($node->args[1]) && $node->args[1] instanceof Arg) {
                                $levelsValue = $evaluator?->evaluateSilently($node->args[1]->value);

                                if (\is_int($levelsValue) && $levelsValue > 0) {
                                    $levels = $levelsValue;
                                }
                            }

                            return dirname($resolved, $levels);
                        }
                    }
                }
            }

            throw new ConstExprEvaluationException(
                sprintf('Expression of type %s cannot be evaluated', $node->getType()),
            );
        });

        try {
            $result = $evaluator->evaluateSilently($expr);

            return \is_string($result) ? $result : null;
        } catch (ConstExprEvaluationException) {
            return null;
        }
    }
}
