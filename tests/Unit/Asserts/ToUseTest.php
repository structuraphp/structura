<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Testing\ArchitectureAsserts;
use Structura\Tests\Fixture\Concerns\HasFactory;

class ToUseTest extends TestCase
{
    use ArchitectureAsserts;
    /*
        #[DataProvider('getClassLikeWithTrait')]
        public function testToExtend(string $raw): void
        {
            $this
                ->allClasses()
                ->fromRaw($raw)
                ->should(
                    static fn(Expr $assert): Expr => $assert
                        ->toUse(HasFactory::class),
                )
                ->assert()
                ->assertArchitecture();
        }*/

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testShouldFailToExtendsWithInterface(
        string $raw,
        string $exceptName = 'Foo',
    ): void {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must use traits <promote>%s</promote>',
                $exceptName,
                HasFactory::class,
            ),
        );
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toUse(HasFactory::class),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class { use \Structura\Tests\Fixture\Concerns\HasFactory; };'];
        yield 'class' => ['<?php class Foo { use Structura\Tests\Fixture\Concerns\HasFactory; }'];
        yield 'enum' => ['<?php enum Foo { use Structura\Tests\Fixture\Concerns\HasFactory; };'];
        yield 'interface' => ['<?php interface Foo { use Structura\Tests\Fixture\Concerns\HasFactory; }'];
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {};'];
        yield 'interface' => ['<?php interface Foo {}'];
    }
}
