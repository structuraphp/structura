<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Fidry\CpuCoreCounter\CpuCoreCounter;
use InvalidArgumentException;

/**
 * Resolves the requested number of analysis processes to a concrete count.
 *
 * "auto" is resolved here, at the CLI and configuration boundary, so that everything downstream
 * only ever sees a concrete integer greater than or equal to 1.
 *
 * Detection is deliberately not "one worker per logical thread": every extra worker costs a PHP
 * boot, a configuration reload and a share of the parent polling loop, so asking for more of them
 * than the test suite can keep busy makes the analysis slower rather than faster. The count is
 * therefore derived from the amount of work available and then capped, the way PHPStan's
 * Parallel\Scheduler does.
 */
final readonly class ProcessCountResolver
{
    /** @var string */
    public const AUTO = 'auto';

    /** @var int hard ceiling on auto-detected processes, mirroring PHPStan's maximumNumberOfProcesses */
    public const MAX_AUTO_PROCESSES = 8;

    /** @var int test classes an auto-detected process should get before one more is spawned */
    private const MIN_JOBS_PER_PROCESS = 2;

    public function __construct(
        private CpuCoreCounter $cpuCoreCounter = new CpuCoreCounter(),
    ) {}

    /**
     * Resolves the CLI value, falling back to the configured count when the option is absent.
     *
     * An explicit count is never capped: it is a deliberate request, and WorkerPool already
     * bounds it by the size of the queue so no more workers than test classes ever start.
     *
     * @param null|string $requested the raw --processes value, a positive integer or "auto"
     * @param int $configured the count declared in structura.php, 1 when not set
     * @param null|int $availableJobs number of test classes to analyse, null when not known yet
     *
     * @throws InvalidArgumentException when the requested value is neither "auto" nor a positive integer
     */
    public function resolve(?string $requested, int $configured = 1, ?int $availableJobs = null): int
    {
        if ($requested === null || $requested === '') {
            return max(1, $configured);
        }

        if ($requested === self::AUTO) {
            return $this->detect($availableJobs);
        }

        if (preg_match('/^[1-9]\d*$/', $requested) !== 1) {
            throw new InvalidArgumentException(
                \sprintf(
                    'The "--processes" option expects a positive integer or "%s", "%s" given.',
                    self::AUTO,
                    $requested,
                ),
            );
        }

        return (int) $requested;
    }

    /**
     * Number of usable cores, capped and never less than 1 when detection is unavailable.
     *
     * @param null|int $availableJobs number of test classes to analyse. When known, the count is
     *                                also bounded so that each process gets at least
     *                                MIN_JOBS_PER_PROCESS classes; callers that resolve before the
     *                                test suite is discovered pass null and only get the ceiling.
     */
    public function detect(?int $availableJobs = null): int
    {
        $processes = min(
            max(1, $this->cpuCoreCounter->getCountWithFallback(1)),
            self::MAX_AUTO_PROCESSES,
        );

        return $availableJobs === null
            ? $processes
            : max(1, min($processes, intdiv($availableJobs, self::MIN_JOBS_PER_PROCESS)));
    }
}
