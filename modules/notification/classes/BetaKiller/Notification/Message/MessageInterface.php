<?php

namespace BetaKiller\Notification\Message;

use BetaKiller\Notification\MessageTargetInterface;

/**
 * Interface MessageInterface
 *
 * @package BetaKiller\Notification
 */
interface MessageInterface
{
    /**
     * @return string
     */
    public static function getCodename(): string;

    /**
     * @return bool
     */
    public static function isCritical(): bool;

    /**
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return callable
     */
    public static function getFactoryFor(MessageTargetInterface $target): callable;

    /**
     * @param array|null $templateData
     * @param array|null $attachments
     *
     * @return static
     */
    public static function create(
        ?array $templateData = null,
        ?array $attachments = null
    ): static;

    /**
     * Returns unique SHA-1 hash based on time, codename, transport and target
     *
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return string
     */
    public static function calculateHashFor(MessageTargetInterface $target): string;

    /**
     * @return array
     */
    public function getTemplateData(): array;

    /**
     * @return array
     */
    public function getAttachments(): array;

    /**
     * @param string $url
     *
     * @return \BetaKiller\Notification\Message\MessageInterface
     */
    public function setActionUrl(string $url): MessageInterface;

    /**
     * @return bool
     */
    public function hasActionUrl(): bool;

    /**
     * @return string
     */
    public function getActionUrl(): string;

    /**
     * Sets optional subject line
     *
     * @param string $value
     *
     * @return \BetaKiller\Notification\Message\MessageInterface
     */
    public function setSubject(string $value): MessageInterface;

    /**
     * Returns optional subject line if exists
     *
     * @return null|string
     */
    public function getSubject(): ?string;

    public function isBroadcast(): bool;
}
