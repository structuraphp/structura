<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use Generator;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\Formatter\Error\ErrorGithubFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorGitlabFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorJsonFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorNoneFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorPrettyJsonFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorTextFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressBarFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressNoneFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Measures the console formatters on a frozen analysis result.
 *
 * The layout mirrors AnalyzeCommand: one output for the whole run, a fresh
 * formatter for each revolution. Formatters accumulate their lines in an
 * internal buffer that is never reset, so reusing an instance would measure a
 * quadratic growth instead of the formatter itself.
 *
 * The output is decorated: the formatters register the StyleCustom styles
 * themselves, so the real ANSI rendering is part of the measurement.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(20)]
#[Iterations(5)]
final class FormatterBench
{
    private AnalyseValueObject $analyseValueObject;

    private BufferedOutput $bufferedOutput;

    private SymfonyStyle $symfonyStyle;

    /** @var class-string<ErrorFormatterInterface|ProgressFormatterInterface> */
    private string $formatter;

    /**
     * @param array{formatter: string} $params
     */
    public function setUp(array $params): void
    {
        $this->analyseValueObject = AnalyseValueObjectFactory::create();
        $this->formatter = self::formatters()[$params['formatter']];

        $this->bufferedOutput = new BufferedOutput();
        $this->bufferedOutput->setDecorated(true);

        $this->symfonyStyle = new SymfonyStyle(new ArrayInput([]), $this->bufferedOutput);
    }

    #[ParamProviders('provideErrorFormatters')]
    public function benchFormatErrors(): void
    {
        /** @var ErrorFormatterInterface $formatter */
        $formatter = new $this->formatter();

        $formatter->formatErrors($this->analyseValueObject, $this->bufferedOutput);

        $this->bufferedOutput->fetch();
    }

    #[ParamProviders('provideProgressFormatters')]
    public function benchProgress(): void
    {
        /** @var ProgressFormatterInterface $formatter */
        $formatter = new $this->formatter();

        $formatter->progressStart($this->symfonyStyle, 3);
        $formatter->progressAdvance($this->symfonyStyle, $this->analyseValueObject);
        $formatter->progressFinish($this->symfonyStyle);

        $this->bufferedOutput->fetch();
    }

    /**
     * @return Generator<string, array{formatter: string}>
     */
    public static function provideErrorFormatters(): Generator
    {
        foreach (self::formatters() as $name => $formatter) {
            if (is_a($formatter, ErrorFormatterInterface::class, true)) {
                yield $name => ['formatter' => $name];
            }
        }
    }

    /**
     * @return Generator<string, array{formatter: string}>
     */
    public static function provideProgressFormatters(): Generator
    {
        foreach (self::formatters() as $name => $formatter) {
            if (is_a($formatter, ProgressFormatterInterface::class, true)) {
                yield $name => ['formatter' => $name];
            }
        }
    }

    /**
     * Every formatter of src/Formatter.
     *
     * @return array<string, class-string<ErrorFormatterInterface|ProgressFormatterInterface>>
     */
    private static function formatters(): array
    {
        return [
            'errorText' => ErrorTextFormatter::class,
            'errorJson' => ErrorJsonFormatter::class,
            'errorPrettyJson' => ErrorPrettyJsonFormatter::class,
            'errorGithub' => ErrorGithubFormatter::class,
            'errorGitlab' => ErrorGitlabFormatter::class,
            'errorNone' => ErrorNoneFormatter::class,
            'progressText' => ProgressTextFormatter::class,
            'progressBar' => ProgressBarFormatter::class,
            'progressNone' => ProgressNoneFormatter::class,
        ];
    }
}
