<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Testing\TestBuilder;
use UnexpectedValueException;

/**
 * Architecture test whose rule definition crashes, to check that the
 * exception reaches the user instead of being taken for a stop-on signal.
 */
final class ThrowingTestBuilder extends TestBuilder
{
    #[TestDox('Crashing rule definition')]
    public function testCrash(): never
    {
        throw new UnexpectedValueException('Rule definition crashed');
    }
}
