<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveAnonymousClass implements ExprScriptInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to have anonymous class';
    }

    public function assert(ScriptDescription $description): bool
    {
        return $description->hasAnonymousClasses();
    }

    public function getViolation(ScriptDescription $description): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must have anonymous class',
                $description->getResourceName(),
            ),
            $this::class,
            $description instanceof ClassDescription
                ? $description->lines
                : 0,
            $description->getFileBasename(),
            $this->message,
        );
    }
}
