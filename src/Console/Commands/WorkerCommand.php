<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use StructuraPhp\Structura\Concerns\Console\LoadsConfig;
use StructuraPhp\Structura\Console\Enums\AnalyseOption;
use StructuraPhp\Structura\Console\Enums\CommonOption;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Services\AnalyseService;
use StructuraPhp\Structura\Services\AnalysisDispatcher;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Services\Parallel\AnalyseResultSerializer;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Child process of a parallel analysis. Never invoked by hand.
 *
 * Reads one NDJSON job per line on STDIN, analyses the requested test class, and writes back a
 * single NDJSON line per job on STDOUT. STDOUT carries the protocol and nothing else; anything
 * diagnostic goes to STDERR.
 */
#[AsCommand(
    name: WorkerCommand::NAME,
    description: 'Internal analysis worker, driven by the parallel orchestrator',
    hidden: true,
)]
final class WorkerCommand extends Command
{
    use LoadsConfig;

    /** @var string */
    public const NAME = 'internal:worker';

    /**
     * Options the parent forwards so the worker analyses exactly what the parent asked for.
     *
     * @var array<int, AnalyseOption>
     */
    private const WORKER_OPTIONS = [
        AnalyseOption::Testsuite,
        AnalyseOption::Filter,
        AnalyseOption::StopOnError,
        AnalyseOption::StopOnWarning,
        AnalyseOption::StopOnNotice,
    ];

    private ConfigValueObject $configValueObject;

    protected function configure(): void
    {
        foreach (self::WORKER_OPTIONS as $option) {
            $this->addOption(
                name: $option->value,
                mode: $option->mode(),
                description: $option->description(),
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $this->optionAsString($input, CommonOption::Config->value);
        if ($configPath === null || !\file_exists($configPath)) {
            fwrite(STDERR, 'Worker started without a readable configuration file.' . PHP_EOL);

            return self::INVALID;
        }

        $this->configValueObject = $this->loadConfigValueObject($configPath);

        // STDOUT is the NDJSON channel: anything the autoload step has to say goes to STDERR,
        // otherwise it would desynchronise the parent's line parser.
        $this->autoloadProject(
            $this->configValueObject,
            new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output),
        );

        // Test classes are loaded with require_once, not through the autoloader, so the whole
        // suite has to be discovered before any of its classes can be reflected upon.
        $knownClasses = $this->discoverClasses($input, $this->configValueObject);

        $serializer = new AnalyseResultSerializer();
        $stdin = \fopen('php://stdin', 'rb');
        if ($stdin === false) {
            fwrite(STDERR, 'Worker could not open STDIN.' . PHP_EOL);

            return self::FAILURE;
        }

        try {
            while (($line = fgets($stdin)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $this->handleJob($input, $line, $knownClasses, $serializer);
            }
        } finally {
            fclose($stdin);
        }

        return self::SUCCESS;
    }

    /**
     * @param array<int, class-string<TestBuilder>> $knownClasses
     */
    private function handleJob(
        InputInterface $input,
        string $line,
        array $knownClasses,
        AnalyseResultSerializer $serializer,
    ): void {
        /** @var mixed $job */
        $job = json_decode($line, true);
        $classname = \is_array($job) && \is_string($job['class'] ?? null) ? $job['class'] : null;

        if ($classname === null) {
            $this->write(['type' => 'error', 'class' => '', 'message' => 'Malformed job: ' . $line]);

            return;
        }

        if (!\in_array($classname, $knownClasses, true)) {
            $this->write([
                'type' => 'error',
                'class' => $classname,
                'message' => \sprintf('Unknown test class "%s" in this test suite.', $classname),
            ]);

            return;
        }

        $service = new AnalyseService(
            dispatcher: new AnalysisDispatcher(),
            stopOnError: $this->optionAsBool($input, AnalyseOption::StopOnError->value),
            stopOnWarning: $this->optionAsBool($input, AnalyseOption::StopOnWarning->value),
            stopOnNotice: $this->optionAsBool($input, AnalyseOption::StopOnNotice->value),
            filter: $this->optionAsString($input, AnalyseOption::Filter->value),
            pathResolvers: $this->configValueObject->pathResolvers,
        );

        $stopOn = false;

        try {
            $result = $service->analyse(0.0, $classname);
        } catch (StopOnException $stopOnException) {
            $stopOn = true;
            $result = $stopOnException->analyseValueObject;
        } catch (Throwable $throwable) {
            $this->write([
                'type' => 'error',
                'class' => $classname,
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);

            return;
        }

        $this->write([
            'type' => 'result',
            'class' => $classname,
            'stopOn' => $stopOn,
            'data' => $serializer->toArray($result),
        ]);
    }

    /**
     * @return array<int, class-string<TestBuilder>>
     */
    private function discoverClasses(InputInterface $input, ConfigValueObject $config): array
    {
        $finder = new FinderService(
            config: $config,
            testSuite: $this->optionAsString($input, AnalyseOption::Testsuite->value),
        );

        return $finder->getClassTests();
    }

    /**
     * @param array<string, mixed> $message
     */
    private function write(array $message): void
    {
        fwrite(STDOUT, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n");
        fflush(STDOUT);
    }

    private function optionAsString(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);

        return \is_string($value) && $value !== '' ? $value : null;
    }

    private function optionAsBool(InputInterface $input, string $name): bool
    {
        return $input->getOption($name) === true;
    }
}
