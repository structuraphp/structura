<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHaveAnonymousClass implements ExprScriptInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to not have anonymous class';
    }

    public function assert(ScriptDescription $description): bool
    {
        return !$description->hasAnonymousClasses();
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $results = [];

        foreach ($description->anonymousClasses as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not have anonymous class',
                    $description->getResourceName(),
                ),
                $this::class,
                $violation->getLine(),
                $description->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}
