<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use InvalidArgumentException;
use StructuraPhp\Structura\Concerns\Console\LoadsConfig;
use StructuraPhp\Structura\Concerns\Console\Version;
use StructuraPhp\Structura\Console\Dtos\AnalyzeDto;
use StructuraPhp\Structura\Console\Enums\AnalyseOption;
use StructuraPhp\Structura\Console\Enums\CommonOption;
use StructuraPhp\Structura\Contracts\AnalyseOrchestratorInterface;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\Enums\ErrorFormatterType;
use StructuraPhp\Structura\Enums\ProgressFormatterType;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Formatter\Error\ErrorGithubFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorGitlabFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorJsonFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorNoneFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorPrettyJsonFormatter;
use StructuraPhp\Structura\Formatter\Error\ErrorTextFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressBarFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressNoneFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Services\AnalyseOrchestrator;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Services\Parallel\ParallelAnalyseOrchestrator;
use StructuraPhp\Structura\Services\ProcessCountResolver;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: AnalyzeCommand::NAME,
    description: 'Test archi',
)]
final class AnalyzeCommand extends Command
{
    use LoadsConfig;
    use Version;

    /** @var string */
    public const NAME = 'analyze';

    private AnalyzeDto $analyzeDto;

    private ConfigValueObject $configValueObject;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->analyzeDto = $this->getAnalyseDto($input);

        if (!\file_exists($this->analyzeDto->configPath)) {
            $initInput = new ArrayInput(['command' => InitCommand::NAME]);
            $this->getApplication()?->doRun($initInput, $output);

            $io->success('Relaunch the command to run your tests');

            return self::SUCCESS;
        }

        $io->writeln($this->getInfos($this->analyzeDto->configPath));
        $io->newLine();

        $this->configValueObject = $this->loadConfigValueObject($this->analyzeDto->configPath);

        $this->autoloadProject($this->configValueObject, $io);
        $errorFormatter = $this->getErrorFormatter();
        $progressFormatter = $this->getProgressFormatter();

        $finder = new FinderService(
            config: $this->configValueObject,
            testSuite: $this->analyzeDto->testSuite,
        );

        $progressFormatter->progressStart($io, count($finder->getClassTests()));

        $orchestrator = $this->getOrchestrator();

        try {
            $result = $orchestrator->run(
                $finder,
                static function (AnalyseValueObject $classResult) use ($progressFormatter, $io): void {
                    $progressFormatter->progressAdvance($io, $classResult);
                },
            );
        } catch (StopOnException $stopOnException) {
            $progressFormatter->progressStopOn($io);

            return $errorFormatter->formatErrors($stopOnException->analyseValueObject, $output);
        }

        $progressFormatter->progressFinish($io);

        return $errorFormatter->formatErrors($result, $output);
    }

    protected function configure(): void
    {
        foreach (AnalyseOption::cases() as $option) {
            $this->addOption(
                name: $option->value,
                shortcut: $option->shortcut(),
                mode: $option->mode(),
                description: $option->description(),
                default: $option->default(),
                suggestedValues: $option->suggestedValues(),
            );
        }
    }

    /**
     * Sequential by default; the parallel orchestrator kicks in only when more than one process
     * is requested, either through --processes or through structura.php.
     */
    private function getOrchestrator(): AnalyseOrchestratorInterface
    {
        $processes = (new ProcessCountResolver())->resolve(
            $this->analyzeDto->processes,
            $this->configValueObject->processes,
        );

        if ($processes === 1) {
            return new AnalyseOrchestrator(
                stopOnError: $this->analyzeDto->stopOnError,
                stopOnWarning: $this->analyzeDto->stopOnWarning,
                stopOnNotice: $this->analyzeDto->stopOnNotice,
                filter: $this->analyzeDto->filter,
                pathResolvers: $this->configValueObject->pathResolvers,
            );
        }

        return new ParallelAnalyseOrchestrator(
            processes: $processes,
            workerOptions: $this->getWorkerOptions(),
        );
    }

    /**
     * Options replayed on every worker so it analyses exactly what this command was asked to.
     *
     * @return array<int, string>
     */
    private function getWorkerOptions(): array
    {
        $options = [
            '--' . CommonOption::Config->value . '=' . $this->analyzeDto->configPath,
        ];

        if (\is_string($this->analyzeDto->testSuite)) {
            $options[] = '--' . AnalyseOption::Testsuite->value . '=' . $this->analyzeDto->testSuite;
        }

        if (\is_string($this->analyzeDto->filter)) {
            $options[] = '--' . AnalyseOption::Filter->value . '=' . $this->analyzeDto->filter;
        }

        if ($this->analyzeDto->stopOnError) {
            $options[] = '--' . AnalyseOption::StopOnError->value;
        }

        if ($this->analyzeDto->stopOnWarning) {
            $options[] = '--' . AnalyseOption::StopOnWarning->value;
        }

        if ($this->analyzeDto->stopOnNotice) {
            $options[] = '--' . AnalyseOption::StopOnNotice->value;
        }

        return $options;
    }

    private function getErrorFormatter(): ErrorFormatterInterface
    {
        if ($this->analyzeDto->noError) {
            return new ErrorNoneFormatter();
        }

        $format = $this->analyzeDto->errorFormat;

        return match ($format) {
            ErrorFormatterType::Text->value => new ErrorTextFormatter(),
            ErrorFormatterType::Github->value => new ErrorGithubFormatter(),
            ErrorFormatterType::Gitlab->value => new ErrorGitlabFormatter(),
            ErrorFormatterType::PrettyJson->value => new ErrorPrettyJsonFormatter(),
            ErrorFormatterType::Json->value => new ErrorJsonFormatter(),
            default => $this->configValueObject->errorFormatter[$format]
                ?? throw new InvalidArgumentException(
                    sprintf('Unknown error format "%s"', $format),
                ),
        };
    }

    private function getProgressFormatter(): ProgressFormatterInterface
    {
        if ($this->analyzeDto->noProgress) {
            return new ProgressNoneFormatter();
        }

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
}
