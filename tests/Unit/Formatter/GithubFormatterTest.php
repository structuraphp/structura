<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter;

use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Formatter\GithubFormatter;
use StructuraPhp\Structura\Services\AnalyseService;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @coversNothing
 */
class GithubFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $service = new AnalyseService(
            StructuraConfig::make()
                ->archiRootNamespace(
                    'StructuraPhp\Structura\Tests\Feature',
                    'tests/Feature',
                ),
        );

        $result = $service->analyse();
        $text = new GithubFormatter();

        $buffer = new BufferedOutput();

        $out = $text->formatErrors($result, $buffer);
        self::assertSame(1, $out);

        $expected = <<<'EOF'
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnAttribut.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnImplementation.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnInheritance.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnUseTrait.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/NotToBeInOneOfTheNamespaces.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\NotToBeInOneOfTheNamespaces</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeAnonymousClasses.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeAnonymousClasses</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeAttribute.php,line=20,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeAttribute</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeBackedEnums.php,line=14,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeBackedEnums</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeClasses.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeClasses</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeEnums.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeEnums</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeFinal.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeFinal</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeInOneOfTheNamespaces.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeInOneOfTheNamespaces</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeInterfaces.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeInterfaces</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeReadonly.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeReadonly</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeTraits.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeTraits</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToExtend.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToExtend</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToExtendNothing.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToExtendNothing</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveAttribute.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveAttribute</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveCorresponding.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveCorresponding</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveCorrespondingClass.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveCorrespondingClass</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveCorrespondingEnum.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveCorrespondingEnum</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveCorrespondingInterface.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveCorrespondingInterface</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveCorrespondingTrait.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveCorrespondingTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveMethod.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveMethod</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveNoAttribute.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveNoAttribute</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveOnlyAttribute.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveOnlyAttribute</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHavePrefix.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHavePrefix</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveSuffix.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveSuffix</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToImplement.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToImplement</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToImplementNothing.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToImplementNothing</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToNotUseTrait.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToNotUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToOnlyImplement.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToOnlyImplement</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToOnlyUseTrait.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToOnlyUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToUseTrait.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnAttribut.php,line=13,col=0::Resource name <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut</promote> must start with <promote>To</promote>
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnImplementation.php,line=13,col=0::Resource name <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation</promote> must start with <promote>To</promote>
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnInheritance.php,line=13,col=0::Resource name <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance</promote> must start with <promote>To</promote>
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOnUseTrait.php,line=13,col=0::Resource name <promote>StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait</promote> must start with <promote>To</promote>
        ::error file=/home/noelma/www/perso/structura/src/Asserts/NotToBeInOneOfTheNamespaces.php,line=12,col=0::Resource name <promote>StructuraPhp\Structura\Asserts\NotToBeInOneOfTheNamespaces</promote> must start with <promote>To</promote>
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/PermissionController.php,line=14,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\PermissionController</promote> must not use a trait
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/PermissionController.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\PermissionController</promote> must have method <promote>__construct</promote>
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/UserController.php,line=9,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\UserController</promote> must have method <promote>__construct</promote>
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/RoleController.php,line=9,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController</promote> must depends only on these namespaces StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory, StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController, StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface, StructuraPhp\Structura\Tests\Fixture\Models\User but depends StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/UserController.php,line=9,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\UserController</promote> must depends only on these namespaces StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory, StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController, StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface, StructuraPhp\Structura\Tests\Fixture\Models\User but depends StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase

        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key]);
        }
    }
}
