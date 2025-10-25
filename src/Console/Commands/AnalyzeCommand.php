<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use Closure;
use InvalidArgumentException;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Dtos\AnalyzeDto;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Enums\FormatterType;
use StructuraPhp\Structura\Formatter\GithubFormatter;
use StructuraPhp\Structura\Formatter\TextFormatter;
use StructuraPhp\Structura\Services\AnalyseService;
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
    private AnalyzeDto $analyzeDto;

    public function getFormatter(InputInterface $input): ErrorFormatterInterface
    {
        return match ($input->getOption('error-format')) {
            FormatterType::Text->value => new TextFormatter(),
            FormatterType::Github->value => new GithubFormatter(),
            default => throw new InvalidArgumentException(),
        };
    }

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

        $io->writeln(\sprintf('Runtime: %-5s PHP %s', '', PHP_VERSION));
        $io->writeln(\sprintf('Configuration: %s', $this->analyzeDto->configPath));
        $io->newLine();

        $structuraConfig = $this->getStructuraConfig();

        $analyseService = new AnalyseService($structuraConfig);
        $result = $analyseService->analyse();

        $formatter = $this->getFormatter($input);
        $formatter->formatErrors($result, $output);

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'error-format',
                shortcut: 'f',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Select output format',
                default: 'text',
                suggestedValues: ['text', 'github'],
            );
    }

    private function getAnalyseDto(InputInterface $input): AnalyzeDto
    {
        /** @var array<string,scalar> $data */
        $data = array_filter(
            array: $input->getOptions(),
            callback: static fn (mixed $value, int|string $key): bool => \is_scalar($value)
                && \is_string($key),
            mode: ARRAY_FILTER_USE_BOTH,
        );

        return AnalyzeDto::fromArray($data);
    }

    private function getStructuraConfig(): StructuraConfig
    {
        /** @var Closure(StructuraConfig): void|StructuraConfig $closure */
        $closure = require $this->analyzeDto->configPath;
        if (!$closure instanceof Closure) {
            throw new InvalidArgumentException();
        }

        $config = new StructuraConfig();
        $closure($config);

        return $config;
    }
}
