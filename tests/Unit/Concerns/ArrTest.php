<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Concerns;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Concerns\Arr;

#[CoversClass(Arr::class)]
final class ArrTest extends TestCase
{
    use Arr;

    public function testImplodeMore(): void
    {
        self::assertSame(
            'acme, foo, bar, [1+]',
            $this->implodeMore(['acme', 'foo', 'bar', 'baz']),
        );
        self::assertSame(
            'acme, foo, bar, baz',
            $this->implodeMore(['acme', 'foo', 'bar', 'baz'], max: 4),
        );
        self::assertSame(
            'acme \ foo \ bar \ [1+]',
            $this->implodeMore(['acme', 'foo', 'bar', 'baz'], glue: ' \ '),
        );
    }
}
