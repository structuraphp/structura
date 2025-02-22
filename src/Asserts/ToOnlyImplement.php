<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToOnlyImplement implements ExprInterface
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
        return \sprintf('to only implement <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->isInterfaceable()
        && \is_array($class->interfaces)
        && \count($class->interfaces) === 1
        && $class->hasInterface($this->name);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must only implement <promote>%s</promote>',
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
