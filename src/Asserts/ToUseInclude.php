<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Expr\Include_;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final class ToUseInclude implements ExprScriptInterface
{
    public function __construct(
        private IncludeType $includeType,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to use <promote>%s</promote>', $this->includeType->label());
    }

    public function assert(ScriptDescription $description): bool
    {
        $notAllowed = array_filter(
            $description->includes,
            fn (Include_ $include): bool => $include->type !== $this->includeType->value,
        );

        return $notAllowed === [];
    }

    public function getViolation(ScriptDescription $description): ViolationValueObject
    {
        return $description instanceof ClassDescription
            ? $this->getViolationClass($description)
            : $this->getViolationScript($description);
    }

    public function getViolationClass(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must use <fire>%s</fire>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->includeType->label(),
            ),
            $this::class,
            0,
            $class->getFileBasename(),
            $this->message,
        );
    }

    private function getViolationScript(ScriptDescription $script): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must use <fire>%s</fire>',
                $script->namespace ?? $script->getFileBasename(),
                $this->includeType->label(),
            ),
            $this::class,
            0,
            $script->getFileBasename(),
            $this->message,
        );
    }
}
