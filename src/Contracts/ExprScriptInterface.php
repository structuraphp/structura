<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

interface ExprScriptInterface extends ExprInterface
{
    public function assert(ScriptDescription $description): bool;

    public function getViolation(ScriptDescription $description): ViolationValueObject;
}
