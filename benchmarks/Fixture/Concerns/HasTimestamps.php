<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Concerns;

use DateTimeImmutable;

trait HasTimestamps
{
    private ?DateTimeImmutable $createdAt = null;

    private ?DateTimeImmutable $updatedAt = null;

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->createdAt ??= $this->updatedAt;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
