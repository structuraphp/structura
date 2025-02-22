<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToImplementNothing implements ExprInterface
{
    public function __construct(
        public readonly string $message,
    ) {}

    public function __toString(): string
    {
        return 'to implement nothing';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->interfaces === null || $class->interfaces === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not implement anything',
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
