<?php
namespace BetaKiller\Notification;

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
    public function getCodename(): string;

    /**
     * Returns unique SHA-1 hash based on time, codename, transport and target
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * @return string
     */
    public function getTransportName(): string;

    /**
     * @return MessageTargetInterface
     */
    public function getFrom(): ?MessageTargetInterface;

    /**
     * @param MessageTargetInterface $value
     *
     * @return MessageInterface
     */
    public function setFrom(MessageTargetInterface $value): MessageInterface;

    /**
     * @return \BetaKiller\Notification\MessageTargetInterface
     */
    public function getTarget(): MessageTargetInterface;

    /**
     * @return array
     */
    public function getAttachments(): array;

    /**
     * @param string $path
     *
     * @return MessageInterface
     */
    public function addAttachment(string $path): MessageInterface;

    /**
     * @param array $data
     *
     * @return MessageInterface
     */
    public function setTemplateData(array $data): MessageInterface;

    /**
     * @return array
     */
    public function getTemplateData(): array;

    /**
     * @param string $url
     *
     * @return \BetaKiller\Notification\MessageInterface
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
     * Returns optional subject line if exists
     *
     * @return null|string
     */
    public function getSubject(): ?string;

    /**
     * Sets optional subject line
     *
     * @param string $value
     *
     * @return \BetaKiller\Notification\MessageInterface
     */
    public function setSubject(string $value): MessageInterface;

    /**
     * @return bool
     */
    public function isCritical(): bool;
}
