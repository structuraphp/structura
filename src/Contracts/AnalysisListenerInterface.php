<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use StructuraPhp\Structura\Events\ExceptEvent;
use StructuraPhp\Structura\Events\NoticeEvent;
use StructuraPhp\Structura\Events\PassEvent;
use StructuraPhp\Structura\Events\ViolationEvent;
use StructuraPhp\Structura\Events\WarningEvent;

interface AnalysisListenerInterface
{
    public function onPass(PassEvent $event): void;

    public function onViolation(ViolationEvent $event): void;

    public function onWarning(WarningEvent $event): void;

    public function onNotice(NoticeEvent $event): void;

    public function onExcept(ExceptEvent $event): void;
}
