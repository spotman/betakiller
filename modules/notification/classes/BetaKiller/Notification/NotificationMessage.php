<?php
namespace BetaKiller\Notification;

/**
 * Class NotificationMessage
 *
 * @package BetaKiller\Notification
 */
class NotificationMessage implements NotificationMessageInterface
{
    private const CODENAME_TEMPLATE = 'a-z0-9-/';

    /**
     * @var string
     */
    private $codename;

    /**
     * @var NotificationTargetInterface
     */
    private $from;

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var NotificationTargetInterface[]
     */
    private $targets = [];

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
     * @param string $codename
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function __construct(string $codename)
    {
        $this->codename = $this->normalizeCodename($codename);
    }

    /**
     * @param string $codename
     *
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function normalizeCodename(string $codename): string
    {
        $codename         = strtolower(trim($codename));
        $codenameTemplate = self::CODENAME_TEMPLATE;
        $codenamePattern  = str_replace('/', '\/', $codenameTemplate);
        preg_match('/[^'.$codenamePattern.']/', $codename, $isCodenameInvalid);
        if ($isCodenameInvalid) {
            throw new NotificationException(
                'Codename ":messageCodename" is invalid. Valid codename template is :codenameTemplate', [
                    ':messageCodename'  => $codename,
                    ':codenameTemplate' => $codenameTemplate,
                ]
            );
        }

        return $codename;
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
    }

    /**
     * @return NotificationTargetInterface
     */
    public function getFrom(): ?NotificationTargetInterface
    {
        return $this->from;
    }

    /**
     * @param NotificationTargetInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function setFrom(NotificationTargetInterface $value): NotificationMessageInterface
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @param NotificationTargetInterface[]|\Iterator $users
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
     * @return NotificationTargetInterface[]
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
     * @param NotificationTargetInterface $value
     *
     * @return NotificationMessageInterface
     */
    public function addTarget(NotificationTargetInterface $value): NotificationMessageInterface
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
     * Returns optional subject line if exists
     *
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Sets optional subject line
     *
     * @param string $value
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function setSubject(string $value): NotificationMessageInterface
    {
        $this->subject = $value;

        return $this;
    }

    /**
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function getBaseI18nKey(): string
    {
        $name = $this->getCodename();

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
     * @param \BetaKiller\Notification\NotificationTargetInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(NotificationTargetInterface $targetUser): array
    {
        return array_merge($this->getTemplateData(), [
            'targetName'  => $targetUser->getFullName(),
            'targetEmail' => $targetUser->getEmail(),
        ]);
    }
}
