<?php

declare(strict_types=1);

namespace Structura\Console\Commands;

use Closure;
use InvalidArgumentException;
use Structura\Configs\StructuraConfig;
use Structura\Console\Dtos\MakeTestDto;
use Structura\ValueObjects\RootNamespaceValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'make',
    description: 'Make test',
)]
class MakeTestCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dto = $this->getDto($input);

        if (!\file_exists($dto->configPath)) {
            return self::INVALID;
        }

        $archiConfig = $this->getStructuraConfig($dto->configPath);

        $io->writeln(\sprintf('Runtime: %-5s PHP %s', '', PHP_VERSION));
        $io->writeln(\sprintf('Configuration: %s', $dto->configPath));

        $rootNamespace = $archiConfig->getArchiRootNamespace();
        if (!$rootNamespace instanceof RootNamespaceValueObject) {
            $io->error('Vous devez définir le root namespace');

            return self::FAILURE;
        }

        $className = rtrim($dto->testClassName, 'Test') . 'Test';

        $configContents = str_replace(
            [
                '{{ namespace }}',
                '{{ class }}',
            ],
            [
                $rootNamespace->namespace,
                $className,
            ],
            (string) file_get_contents(
                \sprintf('%s/Stubs/test.php.dist', \dirname(__DIR__)),
            ),
        );

        (new Filesystem())->dumpFile(
            \sprintf(
                '%s/%s/%s.php',
                getcwd(),
                $rootNamespace->directory,
                $className,
            ),
            $configContents,
        );

        $io->writeln(
            \sprintf('<info>Test file %s is added now</info>', $className),
        );

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Test class name');
    }

    private function getDto(InputInterface $input): MakeTestDto
    {
        /** @var array<string,scalar> $data */
        $data = array_filter(
            array: $input->getOptions() + $input->getArguments(),
            callback: static fn(mixed $value, int|string $key): bool => \is_scalar($value)
                && \is_string($key),
            mode: ARRAY_FILTER_USE_BOTH,
        );

        return MakeTestDto::fromArray($data);
    }

    private function getStructuraConfig(string $configPath): StructuraConfig
    {
        /** @var Closure(StructuraConfig): void|StructuraConfig $closure */
        $closure = require $configPath;
        if (!$closure instanceof Closure) {
            throw new InvalidArgumentException();
        }

        $config = new StructuraConfig();
        $closure($config);

        return $config;
    }
}
