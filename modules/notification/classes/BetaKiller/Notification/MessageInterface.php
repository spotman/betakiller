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
     * @return TargetInterface
     */
    public function getFrom(): ?TargetInterface;

    /**
     * @param TargetInterface $value
     *
     * @return MessageInterface
     */
    public function setFrom(TargetInterface $value): MessageInterface;

    /**
     * @return \BetaKiller\Notification\TargetInterface
     */
    public function getTarget(): TargetInterface;

    /**
     * @param TargetInterface $value
     *
     * @return MessageInterface
     */
    public function setTarget(TargetInterface $value): MessageInterface;

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
     * @param \BetaKiller\Notification\TargetInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(TargetInterface $targetUser): array;
}
