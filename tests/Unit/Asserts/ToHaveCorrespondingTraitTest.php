<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingTrait;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\Tests\Unit\Concerns\ArrTest;
use StructuraPhp\Structura\Tests\Unit\Concerns\Console\VersionTest;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToHaveCorrespondingTrait::class)]
#[CoversMethod(Expr::class, 'toHaveCorrespondingTrait')]
class ToHaveCorrespondingTraitTest extends TestCase
{
    use ArchitectureAsserts;

    private const CORRESPONDENCE_ERROR = [
        ArrTest::class => 'StructuraPhp\Structura\Concerns\ArrError',
        VersionTest::class => 'StructuraPhp\Structura\Concerns\Console\VersionError',
    ];

    public function testToHaveCorrespondingTrait(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__) . '/Concerns')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingTrait(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Unit\\\(.+?)(Test)$/',
                                '$1\\\$2',
                                $classDescription->namespace ?? '',
                            );

                            return is_string($classname)
                                ? $classname
                                : throw new InvalidArgumentException('Classename must be a string');
                        },
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to have corresponding trait',
        );
    }

    public function testShouldFailToHaveCorrespondingTrait(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__) . '/Concerns')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingTrait(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Unit\\\(.+?)(Test)$/',
                                '$1\\\$2Error',
                                $classDescription->namespace ?? '',
                            );

                            return is_string($classname)
                                ? $classname
                                : throw new InvalidArgumentException('Classname not found');
                        },
                    ),
            );

        $output = [];
        foreach (self::CORRESPONDENCE_ERROR as $class => $except) {
            $output[] = sprintf(
                'Resource name <promote>%s</promote> must have corresponding trait <promote>%s</promote>',
                $class,
                $except,
            );
        }

        self::assertRulesViolation(
            $rules,
            implode(', ', $output),
        );
    }
}
