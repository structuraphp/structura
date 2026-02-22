<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Expr\Include_;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final class ToNotUseInclude implements ExprScriptInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            'to not use <promote>%s</promote>',
            implode(
                ', ',
                array_map(fn (IncludeType $include): string => $include->label(), IncludeType::cases()),
            ),
        );
    }

    public function assert(ScriptDescription $description): bool
    {
        return $description->includes === [];
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
                'Resource <promote>%s</promote> must not use anything but use <fire>%s</fire>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->getLables($class),
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
                'Resource <promote>%s</promote> must not use anything but use <fire>%s</fire>',
                $script->namespace ?? $script->getFileBasename(),
                $this->getLables($script),
            ),
            $this::class,
            0,
            $script->getFileBasename(),
            $this->message,
        );
    }

    private function getLables(ScriptDescription $description): string
    {
        return implode(
            ', ',
            array_map(
                fn (Include_ $include) => IncludeType::from($include->type)->label(),
                $description->includes,
            ),
        );
    }
}
