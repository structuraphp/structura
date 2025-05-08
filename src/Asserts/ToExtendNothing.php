<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Name\FullyQualified;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class ToExtendNothing implements ExprInterface
{
    public function __construct(
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to extend nothing';
    }

    public function assert(ClassDescription $class): bool
    {
        return !$class->extends instanceof FullyQualified;
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must extend nothing',
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
