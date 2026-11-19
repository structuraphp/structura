<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Asserts\ToNotDependOn;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Events\ViolationEvent;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

/**
 * Collects the violations of one rule failing on many files, as when Structura is adopted on an existing project:
 * one violation event per file, all under the same rule key.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(5)]
#[Iterations(5)]
final class AssertBuilderBench
{
    private const FILES = 2000;

    private const VIOLATIONS_PER_FILE = 6;

    /** @var array<int, ViolationEvent> */
    private array $events = [];

    public function setUp(): void
    {
        for ($file = 0; $file < self::FILES; $file++) {
            $violations = [];
            for ($dependency = 0; $dependency < self::VIOLATIONS_PER_FILE; $dependency++) {
                $violations[] = new ViolationValueObject(
                    'Resource <promote>App\Foo' . $file . '</promote> must not depend on <fire>Legacy\Bar' . $dependency . '</fire>',
                    ToNotDependOn::class,
                    $dependency + 1,
                    '/app/src/Foo' . $file . '.php',
                    '',
                );
            }

            $this->events[] = new ViolationEvent('to not depend on Legacy', $violations);
        }
    }

    public function benchCollectViolations(): void
    {
        $assertBuilder = new AssertBuilder();
        foreach ($this->events as $event) {
            $assertBuilder->onViolation($event);
        }
    }
}
