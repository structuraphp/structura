<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class NotToBeInOneOfTheNamespaces implements ExprInterface
{
    use Arr;

    /**
     * @param array<int,string> $patterns
     */
    public function __construct(
        private array $patterns,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return sprintf(
            'not to be in one of the namespaces <promote>%s</promote>',
            $this->implodeMore($this->patterns),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        if ($class->isAnonymous()) {
            return true;
        }

        return !$class->hasNamespaceByPatterns($this->patterns);
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not to be in one of the namespaces <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                implode(', ', $this->patterns),
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
