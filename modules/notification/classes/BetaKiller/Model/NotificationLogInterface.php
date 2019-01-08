<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationTargetInterface;
use BetaKiller\Notification\NotificationTransportInterface;
use DateTimeImmutable;

interface NotificationLogInterface extends DispatchableEntityInterface
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
     * @param \BetaKiller\Notification\NotificationTargetInterface $target
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setTarget(NotificationTargetInterface $target): NotificationLogInterface;

    /**
     * @param \BetaKiller\Notification\NotificationTransportInterface $transport
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setTransport(NotificationTransportInterface $transport): NotificationLogInterface;

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
     * @return \BetaKiller\Notification\NotificationTargetInterface
     */
    public function getTargetString(): string;

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
    public function isSucceeded(): bool;

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $value
     *
     * @return NotificationLogInterface
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
}
