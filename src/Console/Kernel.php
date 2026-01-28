<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console;

use StructuraPhp\Structura\Console\Commands\AnalyzeCommand;
use StructuraPhp\Structura\Console\Commands\InitCommand;
use StructuraPhp\Structura\Console\Commands\MakeTestCommand;
use StructuraPhp\Structura\Console\Enums\CommonOption;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Kernel extends Application
{
    public function __construct()
    {
        parent::__construct('Structura');

        $this->addCommands([
            new AnalyzeCommand(),
            new InitCommand(),
            new MakeTestCommand(),
        ]);

        $this->setDefaultCommand(AnalyzeCommand::NAME);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $defaultInputDefinition = parent::getDefaultInputDefinition();
        foreach (CommonOption::cases() as $option) {
            $defaultInputDefinition->addOption(
                new InputOption(
                    name: $option->value,
                    shortcut: $option->shortcut(),
                    mode: $option->mode(),
                    description: $option->description(),
                    default: $option->default(),
                    suggestedValues: $option->suggestedValues(),
                ),
            );
        }

        return $defaultInputDefinition;
    }
}
