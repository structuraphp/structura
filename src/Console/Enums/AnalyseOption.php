<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Enums;

use StructuraPhp\Structura\Enums\ErrorFormatterType;
use StructuraPhp\Structura\Enums\ProgressFormatterType;
use Symfony\Component\Console\Input\InputOption;

enum AnalyseOption: string
{
    case ErrorFormat = 'error-format';
    case ProgressFormat = 'progress-format';
    case Testsuite = 'testsuite';
    case Filter = 'filter';
    case StopOnError = 'stop-on-error';
    case StopOnWarning = 'stop-on-warning';
    case StopOnNotice = 'stop-on-notice';

    public function description(): string
    {
        return match ($this) {
            self::ErrorFormat => 'Select output error format',
            self::ProgressFormat => 'Select output progress format',
            self::Testsuite => 'List available test suites as defined in the PHP configuration file.',
            self::Filter => 'Filter which tests to run using pattern matching on the test name (class or method).',
            self::StopOnError => 'Stop execution upon first that errored.',
            self::StopOnWarning => 'Stop execution after the first warning.',
            self::StopOnNotice => 'Stop execution after the first notice.',
        };
    }

    public function shortcut(): ?string
    {
        return match ($this) {
            self::ErrorFormat => 'f',
            self::ProgressFormat => 'p',
            default => null,
        };
    }

    public function mode(): int
    {
        return match ($this) {
            self::ErrorFormat,
            self::ProgressFormat,
            self::Testsuite,
            self::Filter => InputOption::VALUE_REQUIRED,
            default => InputOption::VALUE_NONE,
        };
    }

    public function default(): ?string
    {
        return match ($this) {
            self::ErrorFormat => ErrorFormatterType::Text->value,
            self::ProgressFormat => ProgressFormatterType::Text->value,
            default => null,
        };
    }

    /**
     * @return array<int,string>
     */
    public function suggestedValues(): array
    {
        return match ($this) {
            self::ErrorFormat => array_column(ErrorFormatterType::cases(), 'value'),
            self::ProgressFormat => array_column(ProgressFormatterType::cases(), 'value'),
            default => [],
        };
    }
}
