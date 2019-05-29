<?php
namespace BetaKiller\Notification;

/**
 * Class Message
 *
 * @package BetaKiller\Notification
 */
class Message implements MessageInterface
{
    private const CODENAME_TEMPLATE = 'a-z0-9-/';

    /**
     * @var string
     */
    private $codename;

    /**
     * @var TargetInterface
     */
    private $from;

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var TargetInterface
     */
    private $target;

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
     * @return TargetInterface
     */
    public function getFrom(): ?TargetInterface
    {
        return $this->from;
    }

    /**
     * @param TargetInterface $value
     *
     * @return MessageInterface
     */
    public function setFrom(TargetInterface $value): MessageInterface
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @return \BetaKiller\Notification\TargetInterface
     */
    public function getTarget(): TargetInterface
    {
        if (!$this->target) {
            throw new NotificationException('Message target must be specified');
        }

        return $this->target;
    }

    /**
     * @param TargetInterface $value
     *
     * @return MessageInterface
     */
    public function setTarget(TargetInterface $value): MessageInterface
    {
        $this->target = $value;

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
     * @return \BetaKiller\Notification\MessageInterface
     */
    public function setSubject(string $value): MessageInterface
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
     * @return MessageInterface
     */
    public function addAttachment(string $path): MessageInterface
    {
        $this->attachments[] = $path;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return MessageInterface
     */
    public function setTemplateData(array $data): MessageInterface
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
     * @param \BetaKiller\Notification\TargetInterface $targetUser
     *
     * @return array
     */
    public function getFullDataForTarget(TargetInterface $targetUser): array
    {
        return array_merge($this->getTemplateData(), [
            'targetName'  => $targetUser->getFullName(),
            'targetEmail' => $targetUser->getEmail(),
        ]);
    }
}
