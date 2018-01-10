<?php
namespace BetaKiller\Notification;

interface NotificationUserInterface
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
     */
    public function isEmailNotificationAllowed(): bool;

    public function enableEmailNotification(): void;

    public function disableEmailNotification(): void;

    /**
     * Returns TRUE if user allowed online notifications through WebSockets/AJAX/etc
     *
     * @return bool
     */
    public function isOnlineNotificationAllowed(): bool;

    /**
     * Return preferred language (used in templates)
     *
     * @return null|string
     */
    public function getLanguageName(): ?string;
}
