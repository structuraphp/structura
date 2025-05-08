<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Commands;

use Closure;
use DateTime;
use Exception;
use InvalidArgumentException;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Dtos\AnalyzeDto;
use StructuraPhp\Structura\Console\Enums\StyleCustom;
use StructuraPhp\Structura\Services\AnalyseService;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @phpstan-import-type ViolationsByTest from \StructuraPhp\Structura\ValueObjects\AnalyseValueObject
 */
#[AsCommand(
    name: 'analyze',
    description: 'Test archi',
)]
class AnalyzeCommand extends Command
{
    private AnalyzeDto $analyzeDto;

    /** @var array<int,string> */
    private array $prints = [];

    public function styleCustom(OutputInterface $output): OutputInterface
    {
        foreach (StyleCustom::cases() as $style) {
            $output
                ->getFormatter()
                ->setStyle(
                    $style->value,
                    $style->getOutputFormatterStyle(),
                );
        }

        return $output;
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

        $timeStart = microtime(true);

        $analyseService = new AnalyseService($structuraConfig);
        $analyseValueObject = $analyseService->analyse();

        $this->failedOutput(array_merge(...$analyseValueObject->violationsByTests));
        $this->assertionsResumeOutput($analyseValueObject);
        $this->durationAndTimeOutput($timeStart);

        $this->prints = array_merge($analyseValueObject->prints, $this->prints);
        foreach ($this->prints as $print) {
            $this->styleCustom($output)->writeln($print);
        }

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

    /**
     * @param ViolationsByTest $violationsByTests
     */
    private function failedOutput(array $violationsByTests): void
    {
        $this->prints[] = '<violation> ERROR LIST </violation>';
        $this->prints[] = '';

        foreach ($violationsByTests as $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $this->prints[] = $violation->messageViolation;
                $this->prints[] = \sprintf(
                    '%s:%d',
                    $violation->pathname,
                    $violation->line,
                );
                $this->prints[] = '';
            }
        }
    }

    private function assertionsResumeOutput(AnalyseValueObject $analyseDto): void
    {
        if ($analyseDto->countPass > 0 && $analyseDto->countViolation === 0) {
            $this->prints[] = \sprintf(
                '%-9s <green>%d passed</green> (%d assertion)',
                'Tests:',
                $analyseDto->countPass,
                $analyseDto->countPass,
            );
        } elseif ($analyseDto->countViolation !== 0) {
            $this->prints[] = \sprintf(
                '%-9s <fire>%d failed</fire>, <green>%d passed</green> (%d assertion)',
                'Tests:',
                $analyseDto->countViolation,
                $analyseDto->countPass,
                $analyseDto->countPass + $analyseDto->countViolation,
            );
        }
    }

    private function durationAndTimeOutput(float $time_start): void
    {
        $timeEnd = microtime(true);
        $time = $timeEnd - $time_start;
        $now = $this->tryDuration($time);

        $this->prints[] = \sprintf(
            'Duration: %s, Memory: %d MB',
            substr($now->format('i:s.u'), 0, -3),
            memory_get_peak_usage(true) / 1024 / 1024,
        );
    }

    private function tryDuration(float $time): DateTime
    {
        $now = DateTime::createFromFormat(
            'U.u',
            number_format($time, 3, '.', ''),
        );

        return $now === false
            ? throw new Exception()
            : $now;
    }
}
