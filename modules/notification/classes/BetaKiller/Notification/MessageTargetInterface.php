<?php
namespace BetaKiller\Notification;

interface MessageTargetInterface
{
    /**
     * Returns identity string
     *
     * @return string
     */
    public function getMessageIdentity(): string;

    /**
     * @return string
     */
    public function getFullName(): string;

    /**
     * Return preferred language (used in templates)
     *
     * @return string
     */
    public function getLanguageIsoCode(): string;
}
