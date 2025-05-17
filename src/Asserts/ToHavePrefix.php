<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHavePrefix implements ExprInterface
{
    public function __construct(
        private string $prefix,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to have prefix <promote>%s</promote>', $this->prefix);
    }

    public function assert(ClassDescription $class): bool
    {
        return str_starts_with($class->name ?? '', $this->prefix);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource name <promote>%s</promote> must start with <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->prefix,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
