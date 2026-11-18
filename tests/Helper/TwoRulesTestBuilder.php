<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

/**
 * Architecture test whose first public method has no TestDox, followed by a method declaring two rules.
 */
final class TwoRulesTestBuilder extends TestBuilder
{
    public function helperWithoutTestDox(): string
    {
        return 'not an architecture test';
    }

    #[TestDox('Two rules')]
    public function testTwoRules(): void
    {
        $this
            ->allClasses()
            ->fromRaw('<?php final class Foo {}')
            ->should(static fn (Expr $assert): Expr => $assert->toBeFinal());

        $this
            ->allClasses()
            ->fromRaw('<?php class Bar {}')
            ->should(static fn (Expr $assert): Expr => $assert->toBeFinal());
    }
}
