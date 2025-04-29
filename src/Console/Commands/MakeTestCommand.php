<?php

declare(strict_types=1);

namespace Structura\Console\Commands;

use Closure;
use Exception;
use InvalidArgumentException;
use Structura\Configs\StructuraConfig;
use Structura\Console\Dtos\MakeTestDto;
use Structura\Services\MakeTestService;
use Structura\ValueObjects\MakeTestValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        $nameResponse = $io->ask('Test name');
        if (!is_string($nameResponse) || $nameResponse === '') {
            $io->error('Test name is required');

            return self::INVALID;
        }

        $pathResponse = $io->ask('Path', 'src');
        if (!is_string($pathResponse) || $pathResponse === '') {
            $io->error('Path is required');

            return self::INVALID;
        }

        $archiConfig = $this->getStructuraConfig($dto->configPath);

        $io->writeln(\sprintf('Runtime: %-5s PHP %s', '', PHP_VERSION));
        $io->writeln(\sprintf('Configuration: %s', $dto->configPath));

        $makeService = new MakeTestService($archiConfig);

        try {
            $makeValueObject = $makeService->make(
                new MakeTestValueObject(
                    testClassName: $nameResponse,
                    path: $pathResponse,
                ),
            );

            $makeService->generate($makeValueObject);
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return self::FAILURE;
        }

        $io->info(
            \sprintf(
                'Test file %s is added now. Run composer dump-autoload',
                $makeValueObject->className,
            ),
        );

        return self::SUCCESS;
    }

    private function getDto(InputInterface $input): MakeTestDto
    {
        /** @var array<string,scalar> $data */
        $data = array_filter(
            array: $input->getOptions() + $input->getArguments(),
            callback: static fn (mixed $value, int|string $key): bool => \is_scalar($value)
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
