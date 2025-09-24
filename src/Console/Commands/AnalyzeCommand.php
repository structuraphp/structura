<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use Closure;
use InvalidArgumentException;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Dtos\AnalyzeDto;
use StructuraPhp\Structura\Services\AnalyseService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'analyze',
    description: 'Test archi',
)]
final class AnalyzeCommand extends Command
{
    private AnalyzeDto $analyzeDto;

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
        $analyseService->analyse();

        return self::SUCCESS;
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
