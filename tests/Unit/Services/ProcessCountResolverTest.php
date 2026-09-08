<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Services\ProcessCountResolver;

#[CoversClass(ProcessCountResolver::class)]
final class ProcessCountResolverTest extends TestCase
{
    #[TestWith(['1', 1])]
    #[TestWith(['2', 2])]
    #[TestWith(['16', 16])]
    public function testExplicitCountWins(string $requested, int $expected): void
    {
        self::assertSame($expected, $this->resolver()->resolve($requested, 8));
    }

    public function testAutoUsesDetectedCoreCount(): void
    {
        self::assertSame(6, $this->resolver(6)->resolve(ProcessCountResolver::AUTO));
    }

    /**
     * Detection can legitimately fail, and a runner that reports zero process would never start.
     */
    public function testDetectionNeverReturnsLessThanOne(): void
    {
        $resolver = new ProcessCountResolver(new CpuCoreCounter([new NullCpuCoreFinder()]));

        self::assertSame(1, $resolver->detect());
        self::assertSame(1, $resolver->resolve(ProcessCountResolver::AUTO));
    }

    #[TestWith([null, 4, 4])]
    #[TestWith(['', 4, 4])]
    #[TestWith([null, 1, 1])]
    public function testFallsBackToConfiguredCount(?string $requested, int $configured, int $expected): void
    {
        self::assertSame($expected, $this->resolver()->resolve($requested, $configured));
    }

    public function testConfiguredCountIsNeverBelowOne(): void
    {
        self::assertSame(1, $this->resolver()->resolve(null, 0));
    }

    #[TestWith(['0'])]
    #[TestWith(['-2'])]
    #[TestWith(['4.5'])]
    #[TestWith(['many'])]
    #[TestWith(['AUTO'])]
    public function testInvalidValueIsRejected(string $requested): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->resolver()->resolve($requested);
    }

    /**
     * Every extra worker costs a PHP boot, a configuration reload and a share of the parent
     * polling loop, so detection is capped instead of following the logical thread count.
     */
    public function testAutoIsCappedAtTheProcessCeiling(): void
    {
        $resolver = $this->resolver(22);

        self::assertSame(ProcessCountResolver::MAX_AUTO_PROCESSES, $resolver->detect());
        self::assertSame(
            ProcessCountResolver::MAX_AUTO_PROCESSES,
            $resolver->resolve(ProcessCountResolver::AUTO),
        );
    }

    /**
     * Asking for more workers than the suite can keep busy only adds startup cost, so the count is
     * also bounded by the number of test classes when they are known.
     */
    #[TestWith([1, 1])]
    #[TestWith([2, 1])]
    #[TestWith([6, 3])]
    #[TestWith([16, 8])]
    #[TestWith([100, 8])]
    public function testAutoIsBoundedByTheNumberOfTestClasses(int $classes, int $expected): void
    {
        self::assertSame(
            $expected,
            $this->resolver(22)->resolve(ProcessCountResolver::AUTO, 1, $classes),
        );
    }

    /**
     * A machine with few cores stays the binding constraint, however large the suite is.
     */
    public function testAutoNeverExceedsTheCoreCount(): void
    {
        self::assertSame(4, $this->resolver(4)->resolve(ProcessCountResolver::AUTO, 1, 100));
    }

    /**
     * An explicit count is a deliberate request: WorkerPool already bounds it by the queue size,
     * so neither the ceiling nor the class count overrides it.
     */
    public function testExplicitCountIsNeverCapped(): void
    {
        self::assertSame(22, $this->resolver()->resolve('22', 1, 6));
    }

    public function testConfiguredCountIsNeverCapped(): void
    {
        self::assertSame(22, $this->resolver()->resolve(null, 22, 6));
    }

    /**
     * @param int<1, max> $cores
     */
    private function resolver(int $cores = 4): ProcessCountResolver
    {
        return new ProcessCountResolver(
            new CpuCoreCounter([new DummyCpuCoreFinder($cores)]),
        );
    }
}
