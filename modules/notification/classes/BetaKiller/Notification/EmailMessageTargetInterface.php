<?php
namespace BetaKiller\Notification;

interface EmailMessageTargetInterface extends MessageTargetInterface
{
    /**
     * Returns target email
     *
     * @return string
     */
    public function getMessageEmail(): string;

    /**
     * Returns TRUE if user allowed notifications through email
     *
     * @return bool
     * @deprecated Move to EmailTransport (separate table for configuring User email notifications)
     */
    public function isEmailNotificationAllowed(): bool;

    /**
     * @deprecated Move to EmailTransport (separate table for configuring User email notifications)
     */
    public function enableEmailNotification(): void;

    /**
     * @deprecated Move to EmailTransport (separate table for configuring User email notifications)
     */
    public function disableEmailNotification(): void;
}
