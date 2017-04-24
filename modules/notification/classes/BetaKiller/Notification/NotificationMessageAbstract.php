<?php
namespace BetaKiller\Notification;

use I18n;

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
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     */
    public function get_subj(NotificationUserInterface $targetUser)
    {
        if (!$this->_subj) {
            return $this->generateSubject($targetUser);
        }

        return $this->_subj;
    }

    protected function generateSubject(NotificationUserInterface $targetUser)
    {
        $key = $this->getBaseI18nKey();
        $key .= '.subj';

        if (!I18n::has($key)) {
            throw new NotificationException('Missing translation for key [:value] in [:lang] language', [
                ':value' => $key,
                ':lang'  => I18n::lang(),
            ]);
        }

        // Getting template data
        $data = $this->getFullData($targetUser);

        // Prefixing data keys with semicolon
        $data = I18n::addColonToKeys($data);

        return __($key, $data);
    }

    protected function getBaseI18nKey()
    {
        $name = $this->get_template_name();

        if (!$name) {
            throw new NotificationException('Can not i18n key from empty template name');
        }

        // Make i18n key by replacing "slash" with "dot"
        return 'notification.'.str_replace('/', '.', $name);
    }

    /**
     * @param string $value
     *
     * @return $this
     * @deprecated Use I18n registry for subject definition (key is based on template path)
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

    protected function getFullData(NotificationUserInterface $targetUser)
    {
        return array_merge($this->get_template_data(), [
            'targetName'  => $targetUser->get_full_name(),
            'targetEmail' => $targetUser->get_email(),
        ]);
    }

    /**
     * @param \BetaKiller\Notification\TransportInterface        $transport
     *
     * @param \BetaKiller\Notification\NotificationUserInterface $target
     *
     * @return string
     */
    public function render(TransportInterface $transport, NotificationUserInterface $target)
    {
        $view = $this->template_factory();

        $data = array_merge($this->getFullData($target), [
            'subject'     => $this->get_subj($target),
            'baseI18nKey' => $this->getBaseI18nKey(),
        ]);

        $data['colonPrefixedData'] = I18n::addColonToKeys($data);

        $view->set($data);

        return $view->render(
            $this->get_template_path().DIRECTORY_SEPARATOR.$this->get_template_name().'-'.$transport->get_name()
        );
    }
}
