<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Console\Enums;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

enum StyleCustom: string
{
    case Fire = 'fire';
    case Green = 'green';
    case Pass = 'pass';
    case Promote = 'promote';
    case Violation = 'violation';

    public function getOutputFormatterStyle(): OutputFormatterStyle
    {
        return match ($this) {
            self::Fire => new OutputFormatterStyle('red', null, ['bold', 'blink']),
            self::Green => new OutputFormatterStyle('green', null, ['bold', 'blink']),
            self::Pass => new OutputFormatterStyle(null, 'green', ['bold', 'blink']),
            self::Promote => new OutputFormatterStyle('#066', null, ['bold', 'blink']),
            self::Violation => new OutputFormatterStyle(null, 'red', ['bold', 'blink']),
        };
    }
}
