<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Jobs;

use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\JobInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\ShouldQueueInterface;

final class PurgeCacheJob implements JobInterface, ShouldQueueInterface
{
    private const PREFIX = 'cache:';

    public function handle(): void
    {
        clearstatcache();
    }

    public function queue(): string
    {
        return self::PREFIX . 'low';
    }
}
