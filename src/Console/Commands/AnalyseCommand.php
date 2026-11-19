<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use Closure;
use InvalidArgumentException;
use StructuraPhp\Structura\Concerns\Console\Version;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Dtos\AnalyseDto;
use StructuraPhp\Structura\Console\Enums\AnalyseOption;
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
use StructuraPhp\Structura\Services\AutoloadService;
use StructuraPhp\Structura\Services\BaselineGenerator;
use StructuraPhp\Structura\Services\BaselineService;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[AsCommand(
    name: AnalyseCommand::NAME,
    description: 'Run the architecture tests',
)]
final class AnalyseCommand extends Command
{
    use Version;

    /** @var string */
    public const NAME = 'analyze';

    private AnalyseDto $analyseDto;

    private ConfigValueObject $configValueObject;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->analyseDto = $this->getAnalyseDto($input);

        if (!\file_exists($this->analyseDto->configPath)) {
            $initInput = new ArrayInput(['command' => InitCommand::NAME]);
            $this->getApplication()?->doRun($initInput, $output);

            $io->success('Relaunch the command to run your tests');

            return self::SUCCESS;
        }

        $io->writeln($this->getInfos($this->analyseDto->configPath));
        $io->newLine();

        $this->configValueObject = $this->getConfigValueObject();

        $this->autoload($io);

        // the baseline paths are relative to the configuration file
        $baseDirectory = \dirname((string) realpath($this->analyseDto->configPath));
        $baselinePath = Path::makeAbsolute(
            $this->configValueObject->baseline ?? 'structura-baseline.php',
            $baseDirectory,
        );

        if ($this->analyseDto->generateBaseline && $this->isPartialRun()) {
            $io->error('--generate-baseline cannot be combined with --filter, --testsuite or --stop-on-* options');

            return self::INVALID;
        }

        $baseline = !$this->analyseDto->generateBaseline && $this->configValueObject->baseline !== null
            ? BaselineService::fromFile($baselinePath, $baseDirectory)
            : null;

        $errorFormatter = $this->getErrorFormatter();
        $progressFormatter = $this->getProgressFormatter();

        $finder = new FinderService(
            config: $this->configValueObject,
            testSuite: $this->analyseDto->testSuite,
        );

        $progressFormatter->progressStart($io, count($finder->getClassTests()));

        $orchestrator = new AnalyseOrchestrator(
            stopOnError: $this->analyseDto->stopOnError,
            stopOnWarning: $this->analyseDto->stopOnWarning,
            stopOnNotice: $this->analyseDto->stopOnNotice,
            filter: $this->analyseDto->filter,
            pathResolvers: $this->configValueObject->pathResolvers,
            baseline: $baseline,
        );

        try {
            $result = $orchestrator->run(
                $finder,
                static function (AnalyseValueObject $classResult) use ($progressFormatter, $io): void {
                    $progressFormatter->progressAdvance($io, $classResult);
                },
            );
        } catch (StopOnException $stopOnException) {
            $progressFormatter->progressStopOn($io);

            $stopped = $stopOnException->analyseValueObject;
            if ($baseline instanceof BaselineService) {
                // the run stopped early: unmatched entries may belong to tests that did not run
                $stopped = $stopped->withBaseline($baseline->countTolerated(), []);
            }

            return $errorFormatter->formatErrors($stopped, $output);
        }

        $progressFormatter->progressFinish($io);

        if ($this->analyseDto->generateBaseline) {
            (new Filesystem())->dumpFile($baselinePath, BaselineGenerator::generate($result, $baseDirectory));
            $io->success(\sprintf('Baseline generated in %s', $baselinePath));

            return self::SUCCESS;
        }

        if ($baseline instanceof BaselineService) {
            $result = $result->withBaseline(
                $baseline->countTolerated(),
                $this->isPartialRun() ? [] : $baseline->getStaleWarnings(),
            );
        }

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

    private function getErrorFormatter(): ErrorFormatterInterface
    {
        if ($this->analyseDto->noError) {
            return new ErrorNoneFormatter();
        }

        $format = $this->analyseDto->errorFormat;

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
        if ($this->analyseDto->noProgress) {
            return new ProgressNoneFormatter();
        }

        $format = $this->analyseDto->progressFormat;

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
     * A partial run may not execute every test: its baseline entries cannot be judged stale.
     */
    private function isPartialRun(): bool
    {
        return $this->analyseDto->filter !== null
            || $this->analyseDto->testSuite !== null
            || $this->analyseDto->stopOnError
            || $this->analyseDto->stopOnWarning
            || $this->analyseDto->stopOnNotice;
    }

    private function autoload(SymfonyStyle $output): void
    {
        if (!str_starts_with(__FILE__, 'phar://')) {
            return;
        }

        (new AutoloadService())->load($this->configValueObject->autoload, $output);
    }

    private function getAnalyseDto(InputInterface $input): AnalyseDto
    {
        return AnalyseDto::fromArray($input->getOptions());
    }

    private function getConfigValueObject(): ConfigValueObject
    {
        /** @var Closure(StructuraConfig): void|StructuraConfig $closure */
        $closure = require $this->analyseDto->configPath;
        if (!$closure instanceof Closure) {
            throw new InvalidArgumentException();
        }

        $config = new StructuraConfig();
        $closure($config);

        return $config->getConfig();
    }
}
