<?php
namespace BetaKiller\Notification;

/**
 * Class NotificationMessageAbstract
 * @package BetaKiller\Notification
 */
abstract class NotificationMessageAbstract implements NotificationMessageInterface
{
    /**
     * @var NotificationUserInterface
     */
    protected $_from;

    /**
     * @var NotificationUserInterface[]
     */
    protected $_to = [];

    /**
     * @var string
     */
    protected $_subj;

    /**
     * @var array
     */
    protected $_attachments = [];

    /**
     * Template codename
     *
     * @var string
     */
    protected $_template_name;

    /**
     * Key => value bindings for template
     *
     * @var array
     */
    protected $_template_data = [];

    /**
     * @return static
     */
    public static function instance()
    {
        return new static;
    }

    /**
     * @return NotificationUserInterface
     */
    public function get_from()
    {
        return $this->_from;
    }

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this
     */
    public function set_from(NotificationUserInterface $value)
    {
        $this->_from = $value;

        return $this;
    }

    /**
     * @return NotificationUserInterface[]
     */
    public function get_to()
    {
        return $this->_to;
    }

    /**
     * @return string[]
     */
    public function get_to_emails()
    {
        $emails = [];

        foreach ($this->get_to() as $to) {
            $emails[] = $to->get_email();
        }

        return $emails;
    }

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this
     */
    public function set_to(NotificationUserInterface $value)
    {
        $this->_to[] = $value;

        return $this;
    }

    /**
     * @param NotificationUserInterface[]|\Iterator $users
     *
     * @return $this
     */
    public function to_users($users)
    {
        foreach ($users as $user) {
            $this->set_to($user);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function get_subj()
    {
        return $this->_subj;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_subj($value)
    {
        $this->_subj = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function get_attachments()
    {
        return $this->_attachments;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function add_attachment($path)
    {
        $this->_attachments[] = $path;

        return $this;
    }

    /**
     * @return int
     */
    public function send()
    {
        return Notification::instance()->send($this);
    }

    /**
     * @param $template_name
     *
     * @return $this|NotificationMessageInterface
     */
    public function set_template_name($template_name)
    {
        $this->_template_name = $template_name;

        return $this;
    }

    /**
     * @return string
     */
    public function get_template_name()
    {
        return $this->_template_name;
    }

    /**
     * @param array $data
     *
     * @return $this|NotificationMessageInterface
     */
    public function set_template_data(array $data)
    {
        $this->_template_data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function get_template_data()
    {
        return $this->_template_data;
    }

    /**
     * @return \View
     */
    protected function template_factory()
    {
        return \View::factory();
    }

    /**
     * @return string
     */
    protected function get_template_path()
    {
        return 'notifications';
    }

    /**
     * @param \BetaKiller\Notification\TransportInterface $transport
     *
     * @return string
     * @throws \View_Exception
     */
    public function render(TransportInterface $transport)
    {
        $view = $this->template_factory();

        $data = array_merge($this->get_template_data(), [
            'to'      => $this->get_to(),
            'subject' => $this->get_subj(),
        ]);

        $view->set($data);

        return $view->render(
            $this->get_template_path().DIRECTORY_SEPARATOR.$this->get_template_name().'-'.$transport->get_name()
        );
    }
}
