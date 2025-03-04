<?php
namespace BetaKiller\Notification;

interface PhoneMessageTargetInterface extends MessageTargetInterface
{
    /**
     * Returns target phone number
     *
     * @return string
     */
    public function getMessagePhone(): string;

    /**
     * Returns TRUE if user allowed notifications through email
     *
     * @return bool
     * @deprecated Move to PhoneTransport (separate table for configuring User phone notifications)
     */
    public function isPhoneNotificationAllowed(): bool;
}
