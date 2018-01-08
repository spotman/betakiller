<?php
namespace BetaKiller\Notification;

use I18n;

/**
 * Class NotificationMessageAbstract
 *
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
     * @var \BetaKiller\Notification\Notification
     */
    private $facade;

    public function __construct(Notification $facade)
    {
        $this->facade = $facade;
    }

    /**
     * @return NotificationUserInterface
     */
    public function getFrom(): ?NotificationUserInterface
    {
        return $this->from;
    }

    /**
     * @param NotificationUserInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function setFrom(NotificationUserInterface $value): NotificationMessageInterface
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @param NotificationUserInterface[]|\Iterator $users
     *
     * @return NotificationMessageInterface
     */
    public function addTargetUsers($users): NotificationMessageInterface
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
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function addTargetEmail(string $email, string $fullName): NotificationMessageInterface
    {
        $target = new NotificationUserEmail($email, $fullName);

        return $this->addTarget($target);
    }

    /**
     * @return NotificationUserInterface[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * @return string[]
     */
    public function getTargetsEmails(): array
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
     * @return NotificationMessageInterface
     */
    public function addTarget(NotificationUserInterface $value): NotificationMessageInterface
    {
        $this->targets[] = $value;

        return $this;
    }

    /**
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function clearTargets(): NotificationMessageInterface
    {
        $this->targets = [];

        return $this;
    }

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function getSubj(NotificationUserInterface $targetUser): string
    {
        if (!$this->subj) {
            return $this->generateSubject($targetUser);
        }

        return $this->subj;
    }

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    protected function generateSubject(NotificationUserInterface $targetUser): string
    {
        $key = $this->getBaseI18nKey();
        $key .= '.subj';

        // Getting template data
        $data = $this->getFullData($targetUser);

        $output = __($key, $data);

        if ($output === $key) {
            throw new NotificationException('Missing translation for key [:value] in [:lang] language', [
                ':value' => $key,
                ':lang'  => I18n::lang(),
            ]);
        }

        return $output;
    }

    /**
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    protected function getBaseI18nKey(): string
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
     * @return NotificationMessageInterface
     * @deprecated Use I18n registry for subject definition (key is based on template path)
     */
    public function setSubj(string $value): NotificationMessageInterface
    {
        $this->subj = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param string $path
     *
     * @return NotificationMessageInterface
     */
    public function addAttachment(string $path): NotificationMessageInterface
    {
        $this->attachments[] = $path;

        return $this;
    }

    /**
     * @return int
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function send(): int
    {
        return $this->facade->send($this);
    }

    /**
     * @param $templateName
     *
     * @return NotificationMessageInterface
     */
    public function setTemplateName(string $templateName): NotificationMessageInterface
    {
        $this->templateName = $templateName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @param array $data
     *
     * @return NotificationMessageInterface
     */
    public function setTemplateData(array $data): NotificationMessageInterface
    {
        $this->templateData = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * @return \View
     */
    protected function template_factory(): \View
    {
        return \View::factory();
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return 'notifications';
    }

    protected function getFullData(NotificationUserInterface $targetUser): array
    {
        return array_merge($this->getTemplateData(), [
            'targetName'  => $targetUser->getFullName(),
            'targetEmail' => $targetUser->getEmail(),
        ]);
    }

    /**
     * @param \BetaKiller\Notification\TransportInterface        $transport
     * @param \BetaKiller\Notification\NotificationUserInterface $target
     *
     * @return string
     * @throws \View_Exception
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function render(TransportInterface $transport, NotificationUserInterface $target): string
    {
        $view = $this->template_factory();

        $data = array_merge($this->getFullData($target), [
            'subject'     => $this->getSubj($target),
            'baseI18nKey' => $this->getBaseI18nKey(),
        ]);

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render(
            $this->getTemplatePath().DIRECTORY_SEPARATOR.$this->getTemplateName().'-'.$transport->getName()
        );
    }
}
