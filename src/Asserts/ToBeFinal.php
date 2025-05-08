<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Enums\FlagType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class ToBeFinal implements ExprInterface
{
    public function __construct(
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be final';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->classType === ClassType::Class_
            && $class->flags & FlagType::ModifierFinal->value;
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must be a final class',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
