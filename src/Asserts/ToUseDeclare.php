<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToUseDeclare implements ExprInterface
{
    public function __construct(
        private string $key,
        private string $value,
        private string $message = '',
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
