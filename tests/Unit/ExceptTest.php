<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Tests\Fixture\Dto\UserDto;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;

#[CoversClass(Except::class)]
final class ExceptTest extends TestCase
{
    public function testByClassnameExclusion(): void
    {
        $except = new Except();
        $except->byClassname(UserDto::class, ToBeFinal::class);

        $classDescription = new ClassDescription(
            namespace: UserDto::class,
            declare: null,
            includes: [],
            anonymousClasses: [],
            name: 'UserDto',
            attrGroups: [],
            lines: 1,
            scalarType: null,
            interfaces: null,
            extends: null,
            traits: [],
            flags: null,
            classType: ClassType::Class_,
            methods: null,
            constants: [],
        );

        self::assertTrue($except->isExcept($classDescription, ToBeFinal::class));
        self::assertFalse($except->isExcept($classDescription, ToBeClasses::class));
    }

    public function testByFileNameWithRegexDelimiters(): void
    {
        $except = new Except();
        $except->byFileName('/migrations\/.*\.php$/', ToBeFinal::class);

        $scriptDescription = new ScriptDescription(
            namespace: 'Migrations',
            declare: null,
            includes: [],
            anonymousClasses: [],
        );
        $scriptDescription->setFilePathname('migrations/2024_01_01_create_users_table.php');

        self::assertTrue($except->isExcept($scriptDescription, ToBeFinal::class));
        self::assertFalse($except->isExcept($scriptDescription, ToBeClasses::class));
    }

    public function testByFileNameExactMatch(): void
    {
        $except = new Except();
        $except->byFileName('Config.php', ToBeFinal::class);

        $scriptDescription = new ScriptDescription(
            namespace: 'App\Config',
            declare: null,
            includes: [],
            anonymousClasses: [],
        );
        $scriptDescription->setFilePathname('Config.php');

        self::assertTrue($except->isExcept($scriptDescription, ToBeFinal::class));
        self::assertFalse($except->isExcept($scriptDescription, ToBeClasses::class));
    }

    public function testByNamespaceExactMatch(): void
    {
        $except = new Except();
        $except->byNamespace('App\Tests', ToBeFinal::class);

        $scriptDescription = new ScriptDescription(
            namespace: 'App\Tests',
            declare: null,
            includes: [],
            anonymousClasses: [],
        );

        self::assertTrue($except->isExcept($scriptDescription, ToBeFinal::class));
        self::assertFalse($except->isExcept($scriptDescription, ToBeClasses::class));
    }

    public function testByNamespaceWithRegex(): void
    {
        $except = new Except();
        $except->byNamespace('App\Tests\.*', ToBeFinal::class);

        $scriptDescription = new ScriptDescription(
            namespace: 'App\Tests\Unit\SomeTest',
            declare: null,
            includes: [],
            anonymousClasses: [],
        );

        self::assertTrue($except->isExcept($scriptDescription, ToBeFinal::class));
    }
}
