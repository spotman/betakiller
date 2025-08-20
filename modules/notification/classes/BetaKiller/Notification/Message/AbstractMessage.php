<?php

namespace BetaKiller\Notification\Message;

use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationException;

/**
 * Class Message
 *
 * @package BetaKiller\Notification
 */
abstract class AbstractMessage implements MessageInterface
{
    private const CODENAME_REGEXP = '/[^a-z0-9-\/]/';

    private ?string $subject;

    private ?string $actionUrl;

    /**
     * @param string $codename
     *
     * @return void
     * @throws \BetaKiller\Notification\NotificationException
     */
    public static function verifyCodename(string $codename): void
    {
        if (preg_match(self::CODENAME_REGEXP, $codename)) {
            throw new NotificationException(
                'Codename ":codename" is invalid. Valid codename template is :regexp', [
                    ':codename' => $codename,
                    ':regexp'   => self::CODENAME_REGEXP,
                ]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public static function create(?array $templateData = null, ?array $attachments = null): static
    {
        return new static($templateData, $attachments);
    }

    /**
     * @param array|null $templateData
     * @param array|null $attachments
     */
    final private function __construct(
        private readonly ?array $templateData = null,
        private readonly ?array $attachments = null
    ) {
    }

    public function isBroadcast(): bool
    {
        return $this instanceof BroadcastMessageInterface;
    }

    /**
     * @inheritDoc
     */
    public static function calculateHashFor(MessageTargetInterface $target): string
    {
        return sha1(
            implode('-', [
                microtime(),
                static::getCodename(),
                $target->getMessageIdentity(),
            ])
        );
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
     * @return \BetaKiller\Notification\Message\MessageInterface
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
        return $this->attachments ?? [];
    }

    /**
     * @return array
     */
    public function getTemplateData(): array
    {
        return $this->templateData ?? [];
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
