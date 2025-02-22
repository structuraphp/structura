<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToUseDeclare implements ExprInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $value,
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to use declare <promote>%s=%s</promote>', $this->key, $this->value);
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->hasDeclare($this->key, $this->value);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must use declaration <promote>%s=%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->key,
                $this->value,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
