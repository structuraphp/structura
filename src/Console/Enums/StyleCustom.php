<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Enums;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\AnsiColorMode;
use Symfony\Component\Console\Terminal;

enum StyleCustom: string
{
    case Fire = 'fire';
    case Green = 'green';
    case Notice = 'notice';
    case Orange = 'orange';
    case Pass = 'pass';
    case Promote = 'promote';
    case Violation = 'violation';
    case Warning = 'warning';
    case Yellow = 'yellow';

    public function getOutputFormatterStyle(): OutputFormatterStyle
    {
        $teal = match (Terminal::getColorMode()) {
            AnsiColorMode::Ansi4 => 'cyan',
            default => '#066',
        };
        $orange =  match (Terminal::getColorMode()) {
            AnsiColorMode::Ansi4 => 'bright-yellow',
            default => '#fa0',
        };

        return match ($this) {
            self::Fire => new OutputFormatterStyle('red', null, ['bold']),
            self::Green => new OutputFormatterStyle('green', null, ['bold']),
            self::Notice => new OutputFormatterStyle(null, $orange, ['bold']),
            self::Orange => new OutputFormatterStyle($orange, null, ['bold']),
            self::Pass => new OutputFormatterStyle(null, 'green', ['bold']),
            self::Promote => new OutputFormatterStyle($teal, null, ['bold']),
            self::Violation => new OutputFormatterStyle(null, 'red', ['bold']),
            self::Warning => new OutputFormatterStyle(null, 'yellow', ['bold']),
            self::Yellow => new OutputFormatterStyle('yellow', null, ['bold']),
        };
    }
}
