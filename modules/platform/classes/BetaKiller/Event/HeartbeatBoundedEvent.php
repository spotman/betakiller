<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\BoundedEventMessageInterface;

final class HeartbeatBoundedEvent implements BoundedEventMessageInterface
{
    private float $ts;

    /**
     * @inheritDoc
     */
    public static function getExternalName(): string
    {
        return 'heartbeat.bounded';
    }

    /**
     * HeartbeatBoundedEvent constructor.
     */
    public function __construct()
    {
        $this->ts = \microtime(true);
    }

    /**
     * @return float
     */
    public function getTimestamp(): float
    {
        return $this->ts;
    }
}
