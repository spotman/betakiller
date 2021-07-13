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
        return 'user.session.opened';
    }

    /**
     * @inheritDoc
     */
    public static function getExternalName(): string
    {
        return 'user.session.opened';
    }
}
