<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use Closure;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveCorrespondingTrait implements ExprInterface
{
    /**
     * @param Closure(ClassDescription): string $callback
     */
    public function __construct(
        private Closure $callback,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to have corresponding trait';
    }

    public function assert(ClassDescription $class): bool
    {
        $callback = $this->callback;
        $className = $callback($class);

        return trait_exists($className);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        $callback = $this->callback;
        $className = $callback($class);

        return new ViolationValueObject(
            \sprintf(
                'Resource name <promote>%s</promote> must have corresponding trait <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $className,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
