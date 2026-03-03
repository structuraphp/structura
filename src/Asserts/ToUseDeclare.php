<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToUseDeclare implements ExprScriptInterface
{
    public function __construct(
        private string $key,
        private string $value,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to use declare <promote>%s=%s</promote>', $this->key, $this->value);
    }

    public function assert(ScriptDescription $description): bool
    {
        return $description->hasDeclare($this->key, $this->value);
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must use declaration <promote>%s=%s</promote>',
                    $description->getResourceName(),
                    $this->key,
                    $this->value,
                ),
                $this::class,
                $description instanceof ClassDescription
                    ? $description->lines
                    : 0,
                $description->getFileBasename(),
                $this->message,
            ),
        ];
    }
}
