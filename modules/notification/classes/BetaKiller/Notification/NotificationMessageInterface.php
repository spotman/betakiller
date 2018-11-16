<?php
namespace BetaKiller\Notification;

/**
 * Interface NotificationMessageInterface
 *
 * @package BetaKiller\Notification
 */
interface NotificationMessageInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @return NotificationTargetInterface
     */
    public function getFrom(): ?NotificationTargetInterface;

    /**
     * @param NotificationTargetInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function setFrom(NotificationTargetInterface $value): NotificationMessageInterface;

    /**
     * @return NotificationTargetInterface[]
     */
    public function getTargets(): array;

    /**
     * @return string[]
     */
    public function getTargetsEmails(): array;

    /**
     * @param NotificationTargetInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function addTarget(NotificationTargetInterface $value): NotificationMessageInterface;

    /**
     * @param NotificationTargetInterface[]|\Iterator $users
     *
     * @return NotificationMessageInterface
     */
    public function addTargetUsers($users): NotificationMessageInterface;

    /**
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function clearTargets(): NotificationMessageInterface;

    /**
     * @return array
     */
    public function getAttachments(): array;

    /**
     * @param string $path
     *
     * @return NotificationMessageInterface
     */
    public function addAttachment(string $path): NotificationMessageInterface;

    /**
     * @param array $data
     *
     * @return NotificationMessageInterface
     */
    public function setTemplateData(array $data): NotificationMessageInterface;

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
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function setSubject(string $value): NotificationMessageInterface;

    /**
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function getBaseI18nKey(): string;

    /**
     * @param \BetaKiller\Notification\NotificationTargetInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(NotificationTargetInterface $targetUser): array;
}
