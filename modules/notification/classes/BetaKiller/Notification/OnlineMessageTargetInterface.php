<?php

namespace BetaKiller\Notification;

interface OnlineMessageTargetInterface extends MessageTargetInterface
{
    /**
     * Returns TRUE if user allowed online notifications through WebSockets/AJAX/etc
     *
     * @return bool
     * @deprecated Move to OnlineTransport (separate table for configuring User email notifications)
     */
    public function isOnlineNotificationAllowed(): bool;
}
