<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingFile;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToHaveCorrespondingFile::class)]
#[CoversMethod(Expr::class, 'toHaveCorrespondingFile')]
final class ToHaveCorrespondingFileTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToHaveCorrespondingFile(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingFile(
                        static fn (ClassDescription $classDescription): string => __FILE__,
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to have corresponding file',
        );
    }

    public function testShouldFailToHaveCorrespondingFile(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingFile(
                        static fn (ClassDescription $classDescription): string => '/nonexistent/path/Foo.php',
                    ),
            );

        self::assertRulesViolation(
            $rules,
            'Resource name <promote>Foo</promote> must have corresponding file <promote>/nonexistent/path/Foo.php</promote>',
        );
    }
}
