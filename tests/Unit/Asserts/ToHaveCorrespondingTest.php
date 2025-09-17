<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveCorresponding;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Enum\UserStatus;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToHaveCorresponding::class)]
#[CoversMethod(Expr::class, 'toHaveCorresponding')]
class ToHaveCorrespondingTest extends TestCase
{
    use ArchitectureAsserts;

    private const CORRESPONDENCE_ERROR = [
        UserStatus::class => 'StructuraPhp\Structura\Tests\Fixture\Models\UserStatusError',
    ];

    public function testToHaveCorresponding(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__, 2) . '/Fixture/Models')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorresponding(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Fixture\\\Models\\\(.+?)$/',
                                '$1\\\Tests\\\Fixture\\\Enum\\\$2Status',
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
            'to have corresponding',
        );
    }

    public function testShouldFailToHaveCorrespondingClass(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__, 2) . '/Fixture/Enum')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorresponding(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Fixture\\\Enum\\\(.+?)$/',
                                '$1\\\Tests\\\Fixture\\\Models\\\$2Error',
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
                'Resource name <promote>%s</promote> must have corresponding <promote>%s</promote>',
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
