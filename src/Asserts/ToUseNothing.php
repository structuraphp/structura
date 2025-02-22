<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Concerns\Arr;
use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ExpectValueObject;
use Structura\ValueObjects\ViolationValueObject;

class ToUseNothing implements ExprInterface
{
    use Arr;

    public function __construct(
        private readonly string $message,
        private readonly ?ExpectValueObject $expect = null,
    ) {}

    public function __toString(): string
    {
        return 'to use nothing';
    }

    public function assert(ClassDescription $class): bool
    {
        if ($class->traits === []) {
            return true;
        }

        if ($this->expect instanceof ExpectValueObject) {
            return $this->first(
                $this->expect->classes,
                static fn(string $value): bool => $class->name === $value,
            );
        }

        return false;
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
