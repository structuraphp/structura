<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

/**
 * @property non-empty-string $suffix
 */
class ToHaveSuffix implements ExprInterface
{
    public function __construct(
        private readonly string $suffix,
        private readonly string $message = '',
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
