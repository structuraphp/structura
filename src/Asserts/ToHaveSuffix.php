<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveSuffix implements ExprInterface
{
    public function __construct(
        private string $suffix,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to have suffix <promote>%s</promote>', $this->suffix);
    }

    public function assert(ClassDescription $class): bool
    {
        return str_ends_with($class->name ?? '', $this->suffix);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource name <promote>%s</promote> must end with <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->suffix,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
