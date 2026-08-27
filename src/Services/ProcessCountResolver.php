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
 */
final readonly class ProcessCountResolver
{
    /** @var string */
    public const AUTO = 'auto';

    public function __construct(
        private CpuCoreCounter $cpuCoreCounter = new CpuCoreCounter(),
    ) {}

    /**
     * Resolves the CLI value, falling back to the configured count when the option is absent.
     *
     * @param null|string $requested the raw --processes value, a positive integer or "auto"
     * @param int $configured the count declared in structura.php, 1 when not set
     *
     * @throws InvalidArgumentException when the requested value is neither "auto" nor a positive integer
     */
    public function resolve(?string $requested, int $configured = 1): int
    {
        if ($requested === null || $requested === '') {
            return max(1, $configured);
        }

        if ($requested === self::AUTO) {
            return $this->detect();
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
     * Number of usable cores, never less than 1 when detection is unavailable.
     */
    public function detect(): int
    {
        return max(1, $this->cpuCoreCounter->getCountWithFallback(1));
    }
}
