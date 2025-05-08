<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class ToHaveAttribute implements ExprInterface
{
    /**
     * @param class-string $name
     */
    public function __construct(
        private readonly string $name,
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to have attribute <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->hasAttribute($this->name);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must have attribute <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->name,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
