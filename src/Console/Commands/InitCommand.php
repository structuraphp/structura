<?php

declare(strict_types=1);

namespace Structura\Console\Commands;

use Structura\Console\Dtos\InitDto;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'init',
    description: 'Initialize config file',
)]
class InitCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dto = $this->getInitDto($input);

        if (\file_exists($dto->configPath)) {
            $io->error(
                \sprintf('Configuration %s already exists', $dto->configPath),
            );

            return self::INVALID;
        }

        /** @var bool $response */
        $response = $io->ask(
            \sprintf(
                'No "%s" config found. Should we generate it for you?',
                $dto->configPath,
            ),
            'yes',
            static fn(string $value): bool => \in_array($value, ['y', 'yes'], true),
        );

        if (!$response) {
            return self::INVALID;
        }

        $configContents = (string) file_get_contents(
            \sprintf('%s/Stubs/structura.php.dist', \dirname(__DIR__)),
        );

        (new Filesystem())->dumpFile($dto->configPath, $configContents);

        $io->success('The config is added now.');

        return self::SUCCESS;
    }

    private function getInitDto(InputInterface $input): InitDto
    {
        /** @var array<string,scalar> $data */
        $data = array_filter(
            array: $input->getOptions(),
            callback: static fn(mixed $value, int|string $key): bool => \is_scalar($value)
                && \is_string($key),
            mode: ARRAY_FILTER_USE_BOTH,
        );

        return InitDto::fromArray($data);
    }
}
