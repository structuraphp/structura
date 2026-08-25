<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Services;

use DateTimeImmutable;
use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\EventInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Enums\Weekday;

final class ReportBuilder
{
    /** @var array<int, string> */
    private array $rows = [];

    public function append(string $row): self
    {
        $this->rows[] = htmlspecialchars(trim($row), ENT_QUOTES);

        return $this;
    }

    public function emptyEvent(): EventInterface
    {
        return new class implements EventInterface {
            public function occurredAt(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
    }

    /**
     * @return array<int, string>
     */
    public function build(Weekday $weekday): array
    {
        return array_filter(
            $this->rows,
            static fn (string $row): bool => $row !== '' && $weekday->isStartOfWeek(),
        );
    }
}
