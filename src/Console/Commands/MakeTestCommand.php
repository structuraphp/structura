<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use Closure;
use Exception;
use InvalidArgumentException;
use StructuraPhp\Structura\Concerns\Console\Version;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Dtos\MakeTestDto;
use StructuraPhp\Structura\Services\MakeTestService;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use StructuraPhp\Structura\ValueObjects\MakeTestValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: MakeTestCommand::NAME,
    description: 'Make test',
)]
final class MakeTestCommand extends Command
{
    use Version;

    /** @var string */
    public const NAME = 'make:test';

    private MakeTestDto $makeTestDto;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->makeTestDto = $this->geMaketDto($input);

        if (!\file_exists($this->makeTestDto->configPath)) {
            return self::INVALID;
        }

        $io->writeln($this->getInfos($this->makeTestDto->configPath));

        $structuraConfig = $this->getConfigValueObject();

        $makeService = new MakeTestService($structuraConfig);

        try {
            $nameResponse = $io->ask(
                'What is the name of the test class (e.g. "NamespaceName\ClassName")?',
            );
            $name = $makeService->getNamespace($nameResponse);

            /** @var string $pathResponse */
            $pathResponse = $io->ask(
                'Source code path that your test will analyze',
                'src',
            );
            $path = $makeService->getPath($pathResponse);
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return self::INVALID;
        }

        try {
            $makeValueObject = $makeService->make(
                new MakeTestValueObject(
                    testClassName: $name,
                    path: $path,
                ),
            );

            $makeService->generate($makeValueObject);
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return self::FAILURE;
        }

        $io->info(
            sprintf(
                <<<'INFO'
                Test file is added now, run composer dump-autoload.
                file://%s
                INFO,
                $makeValueObject->filename,
            ),
        );

        return self::SUCCESS;
    }

    private function geMaketDto(InputInterface $input): MakeTestDto
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

    private function getConfigValueObject(): ConfigValueObject
    {
        /** @var Closure(StructuraConfig): void|StructuraConfig $closure */
        $closure = require $this->makeTestDto->configPath;
        if (!$closure instanceof Closure) {
            throw new InvalidArgumentException();
        }

        $config = new StructuraConfig();
        $closure($config);

        return $config->getConfig();
    }
}
