<?php
declare(strict_types=1);

namespace BetaKiller\Auth\Event;

final class UserSessionOpenedEvent extends AbstractUserSessionEvent
{
    /**
     * @return string
     */
    public function getExternalName(): string
    {
        return 'session.opened';
    }
}
