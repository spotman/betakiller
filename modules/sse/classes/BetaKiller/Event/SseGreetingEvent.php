<?php

declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\AnyTargetRestriction;
use BetaKiller\MessageBus\RestrictionInterface;

final readonly class SseGreetingEvent implements ServerSentEventInterface
{
    use PlainServerSentEventTrait;

    public static function getExternalName(): string
    {
        return 'sse.greeting';
    }

    public function getRestriction(): RestrictionInterface
    {
        return new AnyTargetRestriction();
    }
}
