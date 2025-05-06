<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToOnlyUseTrait implements ExprInterface
{
    /**
     * @param class-string $name
     */
    public function __construct(
        private readonly string $name,
        private readonly string $message,
    ) {}

    public function __toString(): string
    {
        return \sprintf('to only use trait <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->hasTrait($this->name);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> should only use trait <promote>%s</promote>',
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
