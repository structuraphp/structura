<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\ExprScript;

use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprIteratorAggregate;

/**
 * @mixin ExprIteratorAggregate<ExprInterface>
 */
trait DeclareAssert
{
    public function toUseStrictTypes(string $message = ''): self
    {
        return $this->toUseDeclare('strict_types', '1', $message);
    }

    public function toUseDeclare(string $key, string $value, string $message = ''): self
    {
        return $this->addExpr(new ToUseDeclare($key, $value, $message));
    }
}
