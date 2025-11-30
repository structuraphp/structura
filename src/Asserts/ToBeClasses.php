<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToBeClasses implements ExprInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be classes';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->classType === ClassType::Class_;
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must be a class but is <fire>%s</fire>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $class->classType->label(),
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
