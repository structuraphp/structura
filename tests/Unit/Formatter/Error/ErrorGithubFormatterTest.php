<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Formatter\Error\ErrorGithubFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ErrorGithubFormatter::class)]
class ErrorGithubFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutput(AnalyseValueObject $except): void
    {
        $text = new ErrorGithubFormatter();

        $buffer = new BufferedOutput();

        $output = $text->formatErrors($except, $buffer);

        $expected = <<<'EOF'
        ::error file=example.php,line=1,col=0::Resource <promote>x</promote> must be a final class
        ::warning ::<promote>ToBeReadonly</promote> exception for <promote>x</promote> is no longer applicable
        ::notice ::error notice

        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        self::assertSame(ErrorGithubFormatter::ERROR, $output);
        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key]);
        }
    }
}
