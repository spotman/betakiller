<?php
namespace BetaKiller\Notification;

interface NotificationMessageInterface
{
    /**
     * @return NotificationUserInterface
     */
    public function get_from();

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this|NotificationMessageInterface
     */
    public function set_from(NotificationUserInterface $value);

    /**
     * @return NotificationUserInterface[]
     */
    public function get_to();

    /**
     * @return string[]
     */
    public function get_to_emails();

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this|NotificationMessageInterface
     */
    public function set_to(NotificationUserInterface $value);

    /**
     * @param NotificationUserInterface[]|\Iterator $users
     *
     * @return $this
     */
    public function to_users($users);

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     */
    public function get_subj(NotificationUserInterface $targetUser);

    /**
     * @param string $value
     *
     * @return $this|NotificationMessageInterface
     * @deprecated Use I18n registry for subject definition (key is based on template path)
     */
    public function set_subj($value);

    /**
     * @return array
     */
    public function get_attachments();

    /**
     * @param string $path
     *
     * @return $this|NotificationMessageInterface
     */
    public function add_attachment($path);

    /**
     * Send current message via default notification instance
     *
     * @return int
     */
    public function send();

    /**
     * @param $template_name
     *
     * @return $this
     */
    public function set_template_name($template_name);

    /**
     * @return string
     */
    public function get_template_name();

    /**
     * @param array $data
     *
     * @return $this
     */
    public function set_template_data(array $data);

    /**
     * @return array
     */
    public function get_template_data();

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
