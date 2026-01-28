<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Enums;

use Symfony\Component\Console\Input\InputOption;

enum CommonOption: string
{
    case Config = 'config';

    public function description(): string
    {
        return match ($this) {
            self::Config => 'Path to config file',
        };
    }

    public function shortcut(): string
    {
        return match ($this) {
            self::Config => 'c',
        };
    }

    /**
     * @return int-mask-of<InputOption::*>
     */
    public function mode(): int
    {
        return match ($this) {
            self::Config => InputOption::VALUE_REQUIRED,
        };
    }

    public function default(): string
    {
        return match ($this) {
            self::Config => \getcwd() . '/structura.php',
        };
    }

    /**
     * @return array<int,string>
     */
    public function suggestedValues(): array
    {
        return match ($this) {
            self::Config => [],
        };
    }
}
