<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts\ExprScript;

use StructuraPhp\Structura\Enums\IncludeType;

interface ThirdPartyAssertInterface
{
    public function toUseStrictTypes(string $message = ''): self;

    public function toUseDeclare(string $key, string $value, string $message = ''): self;

    public function toUseInclude(IncludeType $includeType, string $message = ''): self;

    public function toNotUseInclude(string $message = ''): self;

    public function toHaveAnonymousClass(string $message = ''): self;

    public function toNotHaveAnonymousClass(string $message = ''): self;

    public function toHaveFilePermission(string $expectedPermission, string $message = ''): self;

    public function toReturnArray(string $message = ''): self;
}
