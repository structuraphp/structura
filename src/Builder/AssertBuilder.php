<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use StructuraPhp\Structura\Contracts\AnalysisListenerInterface;
use StructuraPhp\Structura\Events\ExceptEvent;
use StructuraPhp\Structura\Events\NoticeEvent;
use StructuraPhp\Structura\Events\PassEvent;
use StructuraPhp\Structura\Events\ViolationEvent;
use StructuraPhp\Structura\Events\WarningEvent;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-import-type ViolationsByTest from \StructuraPhp\Structura\ValueObjects\AnalyseValueObject
 */
class AssertBuilder implements AnalysisListenerInterface, EventSubscriberInterface
{
    /** @var array<string,int> */
    private array $pass = [];

    /** @var ViolationsByTest */
    private array $violations = [];

    /** @var array<string, array<int, string>> */
    private array $warnings = [];

    /** @var array<string, string> */
    private array $notices = [];

    /** @var array<string, array<int, string>> */
    private array $exceptions = [];

    public static function getSubscribedEvents(): array
    {
        return [
            PassEvent::class => 'onPass',
            ViolationEvent::class => 'onViolation',
            WarningEvent::class => 'onWarning',
            NoticeEvent::class => 'onNotice',
            ExceptEvent::class => 'onExcept',
        ];
    }

    public function onPass(PassEvent $event): void
    {
        $this->pass[$event->key] = 1;
    }

    public function onViolation(ViolationEvent $event): void
    {
        $this->pass[$event->key] = 0;
        $this->violations[$event->key] = array_merge(
            $this->violations[$event->key] ?? [],
            $event->violations,
        );
    }

    public function onWarning(WarningEvent $event): void
    {
        if ($event->isAssertionWarning) {
            $this->pass[$event->key] = 2;
        }

        $this->warnings[$event->key][] = $event->message;
    }

    public function onNotice(NoticeEvent $event): void
    {
        $this->pass[$event->key] = 3;
        $this->notices[$event->key] = $event->message;
    }

    public function onExcept(ExceptEvent $event): void
    {
        if (\is_string($event->key)) {
            $this->exceptions[$event->key][] = $event->message;
        }
    }

    public function getAssertValueObject(): AssertValueObject
    {
        return new AssertValueObject(
            $this->pass,
            $this->violations,
            $this->exceptions,
            $this->warnings,
            $this->notices,
        );
    }
}
