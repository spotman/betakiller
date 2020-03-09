<?php
declare(strict_types=1);

namespace BetaKiller\Auth\Event;

final class UserSessionOpenedEvent extends AbstractUserSessionEvent
{
    /**
     * @return string
     */
    public function getOutboundName(): string
    {
        return 'session.opened';
    }
}
