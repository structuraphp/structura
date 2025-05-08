<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class ToBeAnonymousClasses implements ExprInterface
{
    public function __construct(
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be anonymous classes';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->classType === ClassType::AnonymousClass_;
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must be an anonymous class',
                $class->namespace,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
