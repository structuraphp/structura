<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\Enums\ClassType;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToBeEnums implements ExprInterface
{
    public function __construct(
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be enums';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->classType === ClassType::Enum_;
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must be an enum',
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
