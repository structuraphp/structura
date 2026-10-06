<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Psr\EventDispatcher\EventDispatcherInterface;
use StructuraPhp\Structura\Events\AbstractAnalysisEvent;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class AnalysisDispatcher extends EventDispatcher
{
    private ?SourceTestValueObject $currentSource = null;

    public function __construct(
        private readonly ?EventDispatcherInterface $parent = null,
    ) {
        parent::__construct();
    }

    public function setCurrentSource(?SourceTestValueObject $source): void
    {
        $this->currentSource = $source;
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        if ($event instanceof AbstractAnalysisEvent && $this->currentSource instanceof SourceTestValueObject) {
            $event->source ??= $this->currentSource;
        }

        parent::dispatch($event, $eventName);

        $this->parent?->dispatch($event);

        return $event;
    }
}
