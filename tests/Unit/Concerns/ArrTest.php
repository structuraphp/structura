<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Concerns;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Structura\Concerns\Arr;

#[CoversClass(Arr::class)]
class ArrTest extends TestCase
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
