<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Formatter\TextFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(TextFormatter::class)]
class TextFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutput(AnalyseValueObject $except): void
    {
        $text = new TextFormatter();

        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        $text->formatErrors($except, $buffer);

        $expected = <<<'EOF'
        <violation> ERROR </violation> Asserts architecture rules in TestAssert
        1 classe(s) from
         - raw value
        That
         - to be classes
        Should
         <green>✔</green> to extend <promote>y</promote>
         <green>✔</green> to be readonly <warning>1 warning(s)</warning>
         <fire>✘</fire> to be final <fire>1 error(s)</fire>


        <violation> ERROR LIST </violation>

        Resource <promote>x</promote> must be a final class
        example.php:1

        Tests:    <green>10 passed</green>, <fire>10 failed</fire>, <warning>1 warning</warning> (21 assertion)
        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key], sprintf('Error line %d', $key));
        }
    }
}
