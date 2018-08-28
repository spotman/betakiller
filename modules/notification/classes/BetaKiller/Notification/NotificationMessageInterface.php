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
     * @return NotificationUserInterface
     */
    public function getFrom(): ?NotificationUserInterface;

    /**
     * @param NotificationUserInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function setFrom(NotificationUserInterface $value): NotificationMessageInterface;

    /**
     * @return NotificationUserInterface[]
     */
    public function getTargets(): array;

    /**
     * @return string[]
     */
    public function getTargetsEmails(): array;

    /**
     * @param NotificationUserInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function addTarget(NotificationUserInterface $value): NotificationMessageInterface;

    /**
     * @param NotificationUserInterface[]|\Iterator $users
     *
     * @return NotificationMessageInterface
     */
    public function addTargetUsers($users): NotificationMessageInterface;

    /**
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function clearTargets(): NotificationMessageInterface;

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     */
    public function getSubj(NotificationUserInterface $targetUser): string;

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
     * @return string
     */
    public function getTemplateName(): string;

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
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function getBaseI18nKey(): string;

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(NotificationUserInterface $targetUser): array;
}
