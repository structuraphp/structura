<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveCorrespondingFile;
use StructuraPhp\Structura\Concerns\Expr\CorrespondingAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToNotHaveCorrespondingFile::class)]
#[CoversMethod(CorrespondingAssert::class, 'toNotHaveCorrespondingFile')]
final class ToNotHaveCorrespondingFileTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToNotHaveCorrespondingFile(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveCorrespondingFile(
                        static fn (ClassDescription $classDescription): string => '/nonexistent/path/Foo.php',
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to not have corresponding file',
        );
    }

    public function testShouldFailToNotHaveCorrespondingFile(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveCorrespondingFile(
                        static fn (ClassDescription $classDescription): string => __FILE__,
                    ),
            );

        self::assertRulesViolation(
            $rules,
            'Resource name <promote>Foo</promote> must not have corresponding file <promote>' . __FILE__ . '</promote>',
        );
    }
}
