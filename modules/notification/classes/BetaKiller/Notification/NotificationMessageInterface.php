<?php
namespace BetaKiller\Notification;

interface NotificationMessageInterface
{
    /**
     * @return NotificationUserInterface
     */
    public function getFrom(): NotificationUserInterface;

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
     * @param string $email
     * @param string $fullName
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function addTargetEmail(string $email, string $fullName): NotificationMessageInterface;

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
     * @param string $value
     *
     * @return NotificationMessageInterface
     * @deprecated Use I18n registry for subject definition (key is based on template path)
     */
    public function setSubj(string $value): NotificationMessageInterface;

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
     * Send current message via default notification instance
     *
     * @return int
     */
    public function send(): int;

    /**
     * @param string $templateName
     *
     * @return NotificationMessageInterface
     */
    public function setTemplateName(string $templateName): NotificationMessageInterface;

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
     * Render message for sending via provided transport
     *
     * @param \BetaKiller\Notification\TransportInterface        $transport
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return string
     */
    public function render(TransportInterface $transport, NotificationUserInterface $user): string;
}
