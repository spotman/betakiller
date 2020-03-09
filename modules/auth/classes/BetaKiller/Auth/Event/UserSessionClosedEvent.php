<?php
declare(strict_types=1);

namespace BetaKiller\Auth\Event;

final class UserSessionClosedEvent extends AbstractUserSessionEvent
{
    /**
     * @return string
     */
    public function getOutboundName(): string
    {
        return 'user.session.closed';
    }
}
