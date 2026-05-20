<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveCorresponding;
use StructuraPhp\Structura\Concerns\Expr\CorrespondingAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory;
use StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface;
use StructuraPhp\Structura\Tests\Fixture\Enum\UserStatus;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToHaveCorresponding::class)]
#[CoversMethod(CorrespondingAssert::class, 'toHaveCorresponding')]
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
            $output,
            [7],
        );
    }

    public function testToHaveCorrespondingEnum(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(sprintf('<?php class Foo {}'))
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorresponding(
                        static fn (ClassDescription $class): string => UserStatus::class,
                    ),
            );

        self::assertRulesPass($rules, 'to have corresponding');
    }

    public function testToHaveCorrespondingInterface(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(sprintf('<?php class Foo {}'))
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorresponding(
                        static fn (ClassDescription $class): string => ShouldQueueInterface::class,
                    ),
            );

        self::assertRulesPass($rules, 'to have corresponding');
    }

    public function testToHaveCorrespondingTrait(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw(sprintf('<?php class Foo {}'))
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorresponding(
                        static fn (ClassDescription $class): string => HasFactory::class,
                    ),
            );

        self::assertRulesPass($rules, 'to have corresponding');
    }
}
