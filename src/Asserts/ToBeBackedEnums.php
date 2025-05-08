<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Identifier;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Enums\ScalarType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class ToBeBackedEnums implements ExprInterface
{
    public function __construct(
        private readonly ?ScalarType $scalarType = null,
        private readonly string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be backed enums';
    }

    public function assert(ClassDescription $class): bool
    {
        if ($class->classType !== ClassType::Enum_) {
            return false;
        }

        if (!$class->scalarType instanceof Identifier) {
            return true;
        }

        if ($this->scalarType instanceof ScalarType) {
            return $class->scalarType->toLowerString() === $this->scalarType->value;
        }

        return true;
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
