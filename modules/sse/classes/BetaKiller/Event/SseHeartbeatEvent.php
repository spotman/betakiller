<?php

declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\AnyTargetRestriction;
use BetaKiller\MessageBus\RestrictionInterface;

use function microtime;

final readonly class SseHeartbeatEvent implements ServerSentEventInterface
{
    use PlainServerSentEventTrait;

    private float $ts;

    public static function getExternalName(): string
    {
        return 'sse.heartbeat';
    }

    public function __construct()
    {
        $this->ts = microtime(true);
    }

    /**
     * @return float
     */
    public function getTimestamp(): float
    {
        return $this->ts;
    }

    public function getRestriction(): RestrictionInterface
    {
        return new AnyTargetRestriction();
    }
}
