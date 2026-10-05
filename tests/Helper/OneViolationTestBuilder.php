<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

/**
 * Architecture test producing exactly one violation, to check the stop-on threshold.
 */
final class OneViolationTestBuilder extends TestBuilder
{
    #[TestDox('Single violation')]
    public function testSingleViolation(): void
    {
        $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(static fn (Expr $assert): Expr => $assert->toBeFinal());
    }
}
