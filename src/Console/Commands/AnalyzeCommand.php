<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use Closure;
use InvalidArgumentException;
use StructuraPhp\Structura\Concerns\Console\Version;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Dtos\AnalyzeDto;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\Enums\ErrorFormatterType;
use StructuraPhp\Structura\Enums\ProgressFormatterType;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Formatter\Error\ErrorGithubFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorTextFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressBarFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Services\AnalyseService;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'analyze',
    description: 'Test archi',
)]
final class AnalyzeCommand extends Command
{
    use Version;

    private AnalyzeDto $analyzeDto;

    private ConfigValueObject $configValueObject;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->analyzeDto = $this->getAnalyseDto($input);

        if (!\file_exists($this->analyzeDto->configPath)) {
            $initInput = new ArrayInput(['command' => InitCommand::getDefaultName()]);
            $this->getApplication()?->doRun($initInput, $output);

            $io->success('Relaunch the command to run your tests');

            return self::SUCCESS;
        }

        $io->writeln($this->getInfos($this->analyzeDto->configPath));
        $io->newLine();

        $this->configValueObject = $this->getConfigValueObject();

        $errorFormatter = $this->getErrorFormatter();
        $progressFormatter = $this->getProgressFormatter();

        $finder = new FinderService(
            config: $this->configValueObject,
            testSuite: $this->analyzeDto->testSuite,
        );
        $rules = $finder->getClassTests();

        $progressFormatter->progressStart($io, count($rules));

        $results = [];

        try {
            /** @var class-string<TestBuilder> $ruleClassname */
            foreach ($rules as $ruleClassname) {
                $analyseService = new AnalyseService(
                    stopOnError: $this->analyzeDto->stopOnError,
                    stopOnWarning: $this->analyzeDto->stopOnWarning,
                    stopOnNotice: $this->analyzeDto->stopOnNotice,
                    filter: $this->analyzeDto->filter,
                );
                $analyseResult = $analyseService
                    ->analyse(
                        microtime(true),
                        $ruleClassname,
                    );

                $progressFormatter->progressAdvance($io, $analyseResult);
                $results[] = $analyseResult;
            }
        } catch (StopOnException $stopOnException) {
            $analyseResult = $stopOnException->analyseValueObject;

            $progressFormatter->progressAdvance($io, $analyseResult);
            $results[] = $analyseResult;

            $result = $this->getValuesInfo($results);

            $progressFormatter->progressStopOn($io);

            return $errorFormatter->formatErrors($result, $output);
        }

        $result = $this->getValuesInfo($results);

        $progressFormatter->progressFinish($io);

        return $errorFormatter->formatErrors($result, $output);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: AnalyzeDto::ERROR_FORMAT_OPTION,
                shortcut: 'f',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Select output error format',
                default: ErrorFormatterType::Text->value,
                suggestedValues: array_column(ErrorFormatterType::cases(), 'value'),
            )
            ->addOption(
                name: AnalyzeDto::PROGRESS_FORMAT_OPTION,
                shortcut: 'p',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Select output progress format',
                default: ProgressFormatterType::Text->value,
                suggestedValues: array_column(ProgressFormatterType::cases(), 'value'),
            )
            ->addOption(
                name: AnalyzeDto::TESTSUITE,
                mode: InputOption::VALUE_REQUIRED,
                description: 'List available test suites as defined in the PHP configuration file.',
            )
            ->addOption(
                name: AnalyzeDto::FILTER,
                mode: InputOption::VALUE_REQUIRED,
                description: 'Filter which tests to run using pattern matching on the test name (class or method).',
            )
            ->addOption(
                name: AnalyzeDto::STOP_ON_ERROR,
                mode: InputOption::VALUE_NONE,
                description: 'Stop execution upon first that errored.',
            )
            ->addOption(
                name: AnalyzeDto::STOP_ON_WARNING,
                mode: InputOption::VALUE_NONE,
                description: 'Stop execution after the first warning.',
            )
            ->addOption(
                name: AnalyzeDto::STOP_ON_NOTICE,
                mode: InputOption::VALUE_NONE,
                description: 'Stop execution after the first notice.',
            );
    }

    private function getErrorFormatter(): ErrorFormatterInterface
    {
        $format = $this->analyzeDto->errorFormat;

        return match ($format) {
            ErrorFormatterType::Text->value => new ErrorTextFormatter(),
            ErrorFormatterType::Github->value => new ErrorGithubFormatter(),
            default => $this->configValueObject->errorFormatter[$format]
                ?? throw new InvalidArgumentException(
                    sprintf('Unknown error format "%s"', $format),
                ),
        };
    }

    private function getProgressFormatter(): ProgressFormatterInterface
    {
        $format = $this->analyzeDto->progressFormat;

        return match ($format) {
            ProgressFormatterType::Text->value => new ProgressTextFormatter(),
            ProgressFormatterType::Bar->value => new ProgressBarFormatter(),
            default => $this->configValueObject->progressFormatter[$format]
                ?? throw new InvalidArgumentException(
                    sprintf('Unknown progress format "%s"', $format),
                ),
        };
    }

    /**
     * @param array<int,AnalyseValueObject> $results
     */
    private function getValuesInfo(array $results): AnalyseValueObject
    {
        $countPass = 0;
        $countViolation = 0;
        $countWarning = 0;
        $countNotice = 0;
        $violationsByTests = [];
        $warningsByTests = [];
        $noticesByTests = [];
        $analyseTestValueObjects = [];

        foreach ($results as $result) {
            $countPass += $result->countPass;
            $countViolation += $result->countViolation;
            $countWarning += $result->countWarning;
            $countNotice += $result->countNotice;
            $violationsByTests[] = $result->violationsByTests;
            $warningsByTests[] = $result->warningsByTests;
            $noticesByTests[] = $result->noticeByTests;
            $analyseTestValueObjects[] = $result->analyseTestValueObjects;
        }

        return new AnalyseValueObject(
            timeStart: $results[0]->timeStart ?? 0,
            countPass: $countPass,
            countViolation: $countViolation,
            countWarning: $countWarning,
            countNotice: $countNotice,
            violationsByTests: array_merge(...$violationsByTests),
            warningsByTests: array_merge(...$warningsByTests),
            noticeByTests: array_merge(...$noticesByTests),
            analyseTestValueObjects: array_merge(...$analyseTestValueObjects),
        );
    }

    private function getAnalyseDto(InputInterface $input): AnalyzeDto
    {
        /** @var array<string,null|scalar> $data */
        $data = array_filter(
            array: $input->getOptions(),
            callback: static fn (mixed $value, int|string $key): bool => (\is_scalar($value) || is_null($value))
                && \is_string($key),
            mode: ARRAY_FILTER_USE_BOTH,
        );

        return AnalyzeDto::fromArray($data);
    }

    private function getConfigValueObject(): ConfigValueObject
    {
        /** @var Closure(StructuraConfig): void|StructuraConfig $closure */
        $closure = require $this->analyzeDto->configPath;
        if (!$closure instanceof Closure) {
            throw new InvalidArgumentException();
        }

        $config = new StructuraConfig();
        $closure($config);

        return $config->getConfig();
    }
}
