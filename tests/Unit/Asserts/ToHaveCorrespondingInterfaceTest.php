<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingInterface;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Enum\UserStatus;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToHaveCorrespondingInterface::class)]
#[CoversMethod(Expr::class, 'toHaveCorrespondingInterface')]
class ToHaveCorrespondingInterfaceTest extends TestCase
{
    use ArchitectureAsserts;

    private const CORRESPONDENCE_ERROR = [
        UserStatus::class => 'StructuraPhp\Structura\Tests\Fixture\Contract\Repository\UserStatusRepository',
    ];

    public function testToHaveCorrespondingInterface(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__, 2) . '/Fixture/Models')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingInterface(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Fixture\\\Models\\\(.+?)$/',
                                '$1\\\Tests\\\Fixture\\\Contract\\\Repository\\\$2Repository',
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
            'to have corresponding interface',
        );
    }

    public function testShouldFailToHaveCorrespondingInterface(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__, 2) . '/Fixture/Enum')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingInterface(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Fixture\\\Enum\\\(.+?)$/',
                                '$1\\\Tests\\\Fixture\\\Contract\\\Repository\\\$2Repository',
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
                'Resource name <promote>%s</promote> must have corresponding interface <promote>%s</promote>',
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
