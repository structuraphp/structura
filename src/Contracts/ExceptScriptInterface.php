<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use StructuraPhp\Structura\ValueObjects\ScriptDescription;

interface ExceptScriptInterface
{
    public function except(ExceptScriptInterface $expr, ScriptDescription $description): bool;
}
