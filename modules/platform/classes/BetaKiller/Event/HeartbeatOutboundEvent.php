<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\OutboundEventMessageInterface;

final class HeartbeatOutboundEvent implements OutboundEventMessageInterface
{
    private float $ts;

    /**
     * @inheritDoc
     */
    public static function getExternalName(): string
    {
        return 'heartbeat.outbound';
    }

    /**
     * HeartbeatOutboundEvent constructor.
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

    public function getOutboundName(): string
    {
        return self::getExternalName();
    }

    /**
     * @return array|null
     */
    public function getOutboundData(): ?array
    {
        return [
            'ts' => $this->ts,
        ];
    }
}
