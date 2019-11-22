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
     * @param MessageTargetInterface $value
     *
     * @return MessageInterface
     */
    public function setTarget(MessageTargetInterface $value): MessageInterface;

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
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function getBaseI18nKey(): string;

    /**
     * @param \BetaKiller\Notification\MessageTargetInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(MessageTargetInterface $targetUser): array;
}
