<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\TransportInterface;
use BetaKiller\Search\SearchResultsItemInterface;
use DateTimeImmutable;

interface NotificationLogInterface extends DispatchableEntityInterface, SearchResultsItemInterface
{
    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setProcessedAt(DateTimeImmutable $value): NotificationLogInterface;

    /**
     * @param string $messageName
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setMessageName(string $messageName): NotificationLogInterface;

    /**
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setTarget(MessageTargetInterface $target): NotificationLogInterface;

    /**
     * Returns linked user ID if exists
     *
     * @return string|null
     */
    public function getTargetUserId(): ?string;

    /**
     * @param \BetaKiller\Notification\TransportInterface $transport
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setTransport(TransportInterface $transport): NotificationLogInterface;

    /**
     * @param string $subj
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setSubject(string $subj): NotificationLogInterface;

    /**
     * @return string
     */
    public function getSubject(): ?string;

    /**
     * @param string $body
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setBody(string $body): NotificationLogInterface;

    /**
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function markAsSucceeded(): NotificationLogInterface;

    /**
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function markAsRejected(): NotificationLogInterface;

    /**
     * @param string|null $result
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function markAsFailed(string $result = null): NotificationLogInterface;

    /**
     * @return string|null
     */
    public function getFailureReason(): ?string;

    /**
     * @return \DateTimeImmutable
     */
    public function getProcessedAt(): DateTimeImmutable;

    /**
     * @return string
     */
    public function getMessageName(): string;

    /**
     * @return string
     */
    public function getTargetIdentity(): string;

    /**
     * @return string
     */
    public function getTransportName(): string;

    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @return bool
     */
    public function isPending(): bool;

    /**
     * @return bool
     */
    public function isSucceeded(): bool;

    /**
     * @return bool
     */
    public function isFailed(): bool;

    /**
     * @return bool
     */
    public function isRetryAvailable(): bool;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setHash(string $value): NotificationLogInterface;

    /**
     * @param string $isoCode
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setLanguageIsoCode(string $isoCode): NotificationLogInterface;

    /**
     * @return string
     */
    public function getLanguageIsoCode(): string;

    /**
     * @param string $url
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setActionUrl(string $url): NotificationLogInterface;

    /**
     * @return bool
     */
    public function hasActionUrl(): bool;

    /**
     * @return string
     */
    public function getActionUrl(): string;

    /**
     * Mark notification as "read" (email opened, etc)
     */
    public function markAsRead(): void;

    /**
     * Returns "true" if notification was read by target user
     *
     * @return bool
     */
    public function isRead(): bool;

    /**
     * @return \DateTimeImmutable
     */
    public function getReadAt(): DateTimeImmutable;
}
