<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Stmt\Class_;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHaveAnonymousClass implements ExprScriptInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to not have anonymous class';
    }

    public function assert(ScriptDescription $description): bool
    {
        return !$description->hasAnonymousClasses();
    }

    public function getViolation(ScriptDescription $description): ViolationValueObject
    {
        return $description instanceof ClassDescription
            ? $this->getViolationClass($description)
            : $this->getViolationScript($description);
    }

    private function getViolationClass(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not have anonymous class but found <fire>%d</fire>',
                $class->getResourceName(),
                $class->countAnonymousClasses(),
            ),
            $this::class,
            $this->getLines($class->anonymousClasses),
            $class->getFileBasename(),
            $this->message,
        );
    }

    private function getViolationScript(ScriptDescription $script): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not have anonymous class but found <fire>%d</fire>',
                $script->namespace ?? $script->getFileBasename(),
                $script->countAnonymousClasses(),
            ),
            $this::class,
            $this->getLines($script->anonymousClasses),
            $script->getFileBasename(),
            $this->message,
        );
    }

    /**
     * @param array<int,Class_> $anonymousClasses
     */
    private function getLines(array $anonymousClasses): int
    {
        return $anonymousClasses[0]->getLine();
    }
}
