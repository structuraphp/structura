<?php

declare(strict_types=1);

namespace Structura\Asserts;

use PhpParser\Node\Identifier;
use Structura\Contracts\ExprInterface;
use Structura\Enums\ClassType;
use Structura\Enums\ScalarType;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

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
