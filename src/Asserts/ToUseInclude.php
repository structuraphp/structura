<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Expr\Include_;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final class ToUseInclude implements ExprScriptInterface
{
    public function __construct(
        private IncludeType $includeType,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to use <promote>%s</promote>', $this->includeType->label());
    }

    public function assert(ScriptDescription $description): bool
    {
        $notAllowed = array_filter(
            $description->includes,
            fn (Include_ $include): bool => $include->type !== $this->includeType->value,
        );

        return $notAllowed === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $violations = array_filter(
            $description->includes,
            fn (Include_ $include): bool => $include->type !== $this->includeType->value,
        );

        $results = [];
        foreach ($violations as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must use <promote>%s</promote> but uses <fire>%s</fire>',
                    $description->getResourceName(),
                    $this->includeType->label(),
                    IncludeType::from($violation->type)->label(),
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
