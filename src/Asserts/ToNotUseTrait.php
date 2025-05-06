<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Concerns\Arr;
use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToNotUseTrait implements ExprInterface
{
    use Arr;

    public function __construct(
        private readonly string $message,
    ) {}

    public function __toString(): string
    {
        return 'to not use trait';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->traits === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not use a trait',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
            ),
            $this::class,
            $class->traits[0]->getLine(),
            $class->getFileBasename(),
            $this->message,
        );
    }
}
