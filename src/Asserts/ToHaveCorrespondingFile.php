<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use Closure;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveCorrespondingFile implements ExprInterface
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
        return 'to have corresponding file';
    }

    public function assert(ClassDescription $class): bool
    {
        $callback = $this->callback;
        $filePath = $callback($class);

        return file_exists($filePath);
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $callback = $this->callback;
        $filePath = $callback($class);

        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource name <promote>%s</promote> must have corresponding file <promote>%s</promote>',
                    $class->getResourceName(),
                    $filePath,
                ),
                $this::class,
                $class->lines,
                $class->getFileBasename(),
                $this->message,
            ),
        ];
    }
}
