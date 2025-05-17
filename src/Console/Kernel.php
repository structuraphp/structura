<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console;

use StructuraPhp\Structura\Console\Commands\AnalyzeCommand;
use StructuraPhp\Structura\Console\Commands\InitCommand;
use StructuraPhp\Structura\Console\Commands\MakeTestCommand;
use StructuraPhp\Structura\Console\Enums\Options;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Kernel extends Application
{
    public function __construct()
    {
        parent::__construct('Structura');

        $this->add(new AnalyzeCommand());
        $this->add(new InitCommand());
        $this->add(new MakeTestCommand());

        $this->setDefaultCommand('analyze');
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $defaultInputDefinition = parent::getDefaultInputDefinition();
        $defaultInputDefinition->addOption(
            new InputOption(
                Options::Config->value,
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to config file',
                $this->getDefaultConfigPath(),
            ),
        );

        return $defaultInputDefinition;
    }

    private function getDefaultConfigPath(): string
    {
        return \getcwd() . '/structura.php';
    }
}
