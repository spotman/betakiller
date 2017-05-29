<?php
namespace BetaKiller\Notification;

interface NotificationMessageInterface
{
    /**
     * @return NotificationUserInterface
     */
    public function getFrom();

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this|NotificationMessageInterface
     */
    public function setFrom(NotificationUserInterface $value);

    /**
     * @return NotificationUserInterface[]
     */
    public function getTargets();

    /**
     * @return string[]
     */
    public function getTargetsEmails();

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this|NotificationMessageInterface
     */
    public function addTarget(NotificationUserInterface $value);

    /**
     * @param string $email
     * @param string $name
     *
     * @return $this|\BetaKiller\Notification\NotificationMessageInterface
     */
    public function addTargetEmail($email, $fullName);

    /**
     * @param NotificationUserInterface[]|\Iterator $users
     *
     * @return $this
     */
    public function addTargetUsers($users);

    /**
     * @return $this|\BetaKiller\Notification\NotificationMessageInterface
     */
    public function clearTargets();

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     */
    public function getSubj(NotificationUserInterface $targetUser);

    /**
     * @param string $value
     *
     * @return $this|NotificationMessageInterface
     * @deprecated Use I18n registry for subject definition (key is based on template path)
     */
    public function setSubj($value);

    /**
     * @return array
     */
    public function getAttachments();

    /**
     * @param string $path
     *
     * @return $this|NotificationMessageInterface
     */
    public function addAttachment($path);

    /**
     * Send current message via default notification instance
     *
     * @return int
     */
    public function send();

    /**
     * @param string $template_name
     *
     * @return $this
     */
    public function setTemplateName($template_name);

    /**
     * @return string
     */
    public function getTemplateName();

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setTemplateData(array $data);

    /**
     * @return array
     */
    public function getTemplateData();

    /**
     * Render message for sending via provided transport
     *
     * @param \BetaKiller\Notification\TransportInterface        $transport
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return string
     */
    public function render(TransportInterface $transport, NotificationUserInterface $user);
}
