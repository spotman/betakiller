<?php
namespace BetaKiller\Notification;

/**
 * Class NotificationMessage
 *
 * @package BetaKiller\Notification
 */
class NotificationMessage implements NotificationMessageInterface
{
    /**
     * @var string
     */
    private $codename;

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
     * Key => value bindings for template
     *
     * @var array
     */
    private $templateData = [];

    /**
     * NotificationMessage constructor.
     *
     * @param string $codename
     */
    public function __construct(string $codename)
    {
        // TODO Sanitize and replace "_" with "/"
        $this->codename = $codename;
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
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
        $data = $this->getFullDataForTarget($targetUser);

        $output = __($key, $data);

        if ($output === $key) {
            throw new NotificationException('Missing translation for key [:value]', [
                ':value' => $key,
            ]);
        }

        return $output;
    }

    /**
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function getBaseI18nKey(): string
    {
        $name = $this->getTemplateName();

        if (!$name) {
            throw new NotificationException('Can not make i18n key from empty template name');
        }

        // Make i18n key by replacing "slash" with "dot"
        return 'notification.'.str_replace('/', '.', $name);
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
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->codename;
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
     * @param \BetaKiller\Notification\NotificationUserInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(NotificationUserInterface $targetUser): array
    {
        return array_merge($this->getTemplateData(), [
            'targetName'  => $targetUser->getFullName(),
            'targetEmail' => $targetUser->getEmail(),
        ]);
    }
}
