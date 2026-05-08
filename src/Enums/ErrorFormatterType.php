<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Enums;

enum ErrorFormatterType: string
{
    case Text = 'text';
    case Github = 'github';
    case Gitlab = 'gitlab';
    case PrettyJson = 'pretty-json';
    case Json = 'json';
}
