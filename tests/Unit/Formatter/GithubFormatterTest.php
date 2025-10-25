<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Formatter\GithubFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(GithubFormatter::class)]
class GithubFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutput(AnalyseValueObject $except): void
    {
        $text = new GithubFormatter();

        $buffer = new BufferedOutput();

        $text->formatErrors($except, $buffer);

        $expected = <<<'EOF'
        ::error file=example.php,line=1,col=0::Resource <promote>x</promote> must be a final class

        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key]);
        }
    }
}
