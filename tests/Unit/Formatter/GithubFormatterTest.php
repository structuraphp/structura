<?php

namespace StructuraPhp\Structura\Tests\Unit\Formatter;

use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Formatter\GithubFormatter;
use StructuraPhp\Structura\Formatter\TextFormatter;
use StructuraPhp\Structura\Services\AnalyseService;
use Symfony\Component\Console\Output\BufferedOutput;

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
        self::assertEquals(1, $out);

        $expected = <<<'EOF'
        ::error file=/home/noelma/www/perso/structura/src/Asserts/DependsOnlyOn.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\DependsOnlyOn</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeAnonymousClasses.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeAnonymousClasses</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeBackedEnums.php,line=14,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeBackedEnums</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeClasses.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeClasses</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeEnums.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeEnums</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeFinal.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeFinal</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeInterfaces.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeInterfaces</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeReadonly.php,line=13,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeReadonly</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToBeTraits.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToBeTraits</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToExtend.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToExtend</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToExtendNothing.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToExtendNothing</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveAttribute.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveAttribute</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveMethod.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveMethod</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHavePrefix.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHavePrefix</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToHaveSuffix.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToHaveSuffix</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToImplement.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToImplement</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToImplementNothing.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToImplementNothing</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToNotDependsOn.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToNotDependsOn</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToNotUseTrait.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToNotUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToOnlyImplement.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToOnlyImplement</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToOnlyUseTrait.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToOnlyUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToUseDeclare.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToUseDeclare</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/src/Asserts/ToUseTrait.php,line=11,col=0::Resource <promote>StructuraPhp\Structura\Asserts\ToUseTrait</promote> must not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/PermissionController.php,line=14,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\PermissionController</promote> must not use a trait
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/PermissionController.php,line=12,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\PermissionController</promote> must have method <promote>__construct</promote>
        ::error file=/home/noelma/www/perso/structura/tests/Fixture/Http/Controller/UserController.php,line=9,col=0::Resource <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\UserController</promote> must have method <promote>__construct</promote>

        EOF;

        $expected = explode(PHP_EOL, $expected);
        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key]);
        }
    }
}