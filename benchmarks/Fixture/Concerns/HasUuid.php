<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Concerns;

trait HasUuid
{
    private string $uuid = '';

    public function uuid(): string
    {
        if ($this->uuid === '') {
            $this->uuid = sprintf('%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)));
        }

        return $this->uuid;
    }
}
