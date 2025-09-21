<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToUseDeclare implements ExprScriptInterface
{
    public function __construct(
        private string $key,
        private string $value,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to use declare <promote>%s=%s</promote>', $this->key, $this->value);
    }

    public function assert(ScriptDescription $description): bool
    {
        return $description->hasDeclare($this->key, $this->value);
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
                'Resource <promote>%s</promote> must use declaration <promote>%s=%s</promote>',
                $class->namespace ?? '',
                $this->key,
                $this->value,
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
                'Resource <promote>%s</promote> must use declaration <promote>%s=%s</promote>',
                $script->namespace ?? '',
                $this->key,
                $this->value,
            ),
            $this::class,
            0,
            $script->getFileBasename(),
            $this->message,
        );
    }
}
