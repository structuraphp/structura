<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final class ToNotUseInclude implements ExprScriptInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            'to not use <promote>%s</promote>',
            implode(
                ', ',
                array_map(fn (IncludeType $include): string => $include->label(), IncludeType::cases()),
            ),
        );
    }

    public function assert(ScriptDescription $description): bool
    {
        return $description->includes === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $results = [];
        foreach ($description->includes as $include) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not use <promote>include* or require*</promote> but use <fire>%s</fire>',
                    $description->getResourceName(),
                    IncludeType::from($include->type)->label(),
                ),
                $this::class,
                $include->getLine(),
                $description->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}
