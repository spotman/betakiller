<?php
namespace BetaKiller\Notification;

interface MessageTargetInterface
{
    /**
     * Returns user email
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * @return string
     */
    public function getFullName(): string;

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

    /**
     * Returns TRUE if user allowed online notifications through WebSockets/AJAX/etc
     *
     * @deprecated Move to OnlineTransport (separate table for configuring User email notifications)
     * @return bool
     */
    public function isOnlineNotificationAllowed(): bool;

    /**
     * Return preferred language (used in templates)
     *
     * @return string
     */
    public function getLanguageIsoCode(): string;
}
