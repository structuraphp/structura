<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Expr\Include_;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
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
        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not use anything but use <fire>%s</fire>',
                    $description->getResourceName(),
                    $this->getLables($description),
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

    private function getLables(ScriptDescription $description): string
    {
        return implode(
            ', ',
            array_map(
                fn (Include_ $include) => IncludeType::from($include->type)->label(),
                $description->includes,
            ),
        );
    }
}
