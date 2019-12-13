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
     * @var MessageTargetInterface
     */
    private $from;

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var MessageTargetInterface
     */
    private $target;

    /**
     * @var string|null
     */
    private $actionUrl;

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

        if (preg_match('/[^'.$codenamePattern.']/', $codename)) {
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
     * @return MessageTargetInterface
     */
    public function getFrom(): ?MessageTargetInterface
    {
        return $this->from;
    }

    /**
     * @param MessageTargetInterface $value
     *
     * @return MessageInterface
     */
    public function setFrom(MessageTargetInterface $value): MessageInterface
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @return \BetaKiller\Notification\MessageTargetInterface
     */
    public function getTarget(): MessageTargetInterface
    {
        if (!$this->target) {
            throw new NotificationException('Message target must be specified');
        }

        return $this->target;
    }

    /**
     * @param MessageTargetInterface $value
     *
     * @return MessageInterface
     */
    public function setTarget(MessageTargetInterface $value): MessageInterface
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
     * @inheritDoc
     */
    public function setActionUrl(string $url): MessageInterface
    {
        $this->actionUrl = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasActionUrl(): bool
    {
        return !empty($this->actionUrl);
    }

    /**
     * @return string
     */
    public function getActionUrl(): string
    {
        return $this->actionUrl;
    }
}
