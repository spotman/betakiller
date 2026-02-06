<?php

declare(strict_types=1);

namespace BetaKiller\Event;

trait PlainServerSentEventTrait
{
    public function getOutboundName(): string
    {
        return self::getExternalName();
    }

    public function getOutboundData(): ?array
    {
        return null;
    }
}
