<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Events;

use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractAnalysisEvent extends Event
{
    public ?SourceTestValueObject $source = null;
}
