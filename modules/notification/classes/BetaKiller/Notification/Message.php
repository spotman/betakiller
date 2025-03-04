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
     * @var string
     */
    private $hash;

    /**
     * @var MessageTargetInterface
     */
    private $target;

    /**
     * @var string
     */
    private $transport;

    /**
     * @var bool
     */
    private $critical;

    /**
     * @var MessageTargetInterface
     */
    private $from;

    /**
     * @var string|null
     */
    private $subject;

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
     * @param string                                          $codename
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param string                                          $transport
     * @param bool                                            $critical
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function __construct(
        string $codename,
        MessageTargetInterface $target,
        string $transport,
        bool $critical
    ) {
        if (!$codename) {
            throw new NotificationException('Message name is missing');
        }

        if (!$transport) {
            throw new NotificationException('Transport is missing for message :name', [
                ':name' => $codename,
            ]);
        }

        $this->codename  = $this->normalizeCodename($codename);
        $this->transport = $transport;
        $this->target    = $target;
        $this->critical  = $critical;

        $this->calculateHash();
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
     * Returns unique SHA-1 hash based on time, codename, transport and target
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getTransportName(): string
    {
        return $this->transport;
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
        return $this->target;
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

    /**
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->critical;
    }

    private function calculateHash(): void
    {
        $this->hash = sha1(implode('-', [
            microtime(),
            $this->getCodename(),
            $this->getTarget()->getMessageIdentity(),
            $this->getTransportName(),
        ]));
    }
}
