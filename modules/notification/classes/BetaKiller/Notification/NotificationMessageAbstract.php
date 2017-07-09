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
    private $from;

    /**
     * @var NotificationUserInterface[]
     */
    private $targets = [];

    /**
     * @var string
     */
    private $subj;

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * Template codename
     *
     * @var string
     */
    private $templateName;

    /**
     * Key => value bindings for template
     *
     * @var array
     */
    private $templateData = [];

    /**
     * @return NotificationUserInterface
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this
     */
    public function setFrom(NotificationUserInterface $value)
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @param NotificationUserInterface[]|\Iterator $users
     *
     * @return $this
     */
    public function addTargetUsers($users)
    {
        foreach ($users as $user) {
            $this->addTarget($user);
        }

        return $this;
    }

    /**
     * @param string      $email
     * @param string|null $fullName
     *
     * @return $this|\BetaKiller\Notification\NotificationMessageInterface
     */
    public function addTargetEmail($email, $fullName)
    {
        $target = new NotificationUserEmail($email, $fullName);
        return $this->addTarget($target);
    }

    /**
     * @return NotificationUserInterface[]
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return string[]
     */
    public function getTargetsEmails()
    {
        $emails = [];

        foreach ($this->getTargets() as $to) {
            $emails[] = $to->getEmail();
        }

        return $emails;
    }

    /**
     * @param NotificationUserInterface $value
     *
     * @return $this
     */
    public function addTarget(NotificationUserInterface $value)
    {
        $this->targets[] = $value;

        return $this;
    }

    /**
     * @return $this|\BetaKiller\Notification\NotificationMessageInterface
     */
    public function clearTargets()
    {
        $this->targets = [];

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     */
    public function getSubj(NotificationUserInterface $targetUser)
    {
        if (!$this->subj) {
            return $this->generateSubject($targetUser);
        }

        return $this->subj;
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
        $name = $this->getTemplateName();

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
    public function setSubj($value)
    {
        $this->subj = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function addAttachment($path)
    {
        $this->attachments[] = $path;

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
    public function setTemplateName($template_name)
    {
        $this->templateName = $template_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param array $data
     *
     * @return $this|NotificationMessageInterface
     */
    public function setTemplateData(array $data)
    {
        $this->templateData = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateData()
    {
        return $this->templateData;
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
        return array_merge($this->getTemplateData(), [
            'targetName'  => $targetUser->getFullName(),
            'targetEmail' => $targetUser->getEmail(),
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
            'subject'     => $this->getSubj($target),
            'baseI18nKey' => $this->getBaseI18nKey(),
        ]);

        $data['colonPrefixedData'] = I18n::addColonToKeys($data);

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render(
            $this->get_template_path().DIRECTORY_SEPARATOR.$this->getTemplateName().'-'.$transport->getName()
        );
    }
}
