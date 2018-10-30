<?php
namespace BetaKiller\Model;

interface PhpExceptionModelInterface extends DispatchableEntityInterface
{
    public const STATE_NEW      = 'new';
    public const STATE_RESOLVED = 'resolved';
    public const STATE_REPEATED = 'repeated';
    public const STATE_IGNORED  = 'ignored';

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $value
     *
     * @return PhpExceptionModelInterface
     */
    public function setHash(string $value): PhpExceptionModelInterface;

    /**
     * @param string $module
     *
     * @return PhpExceptionModelInterface
     */
    public function addModule(string $module): PhpExceptionModelInterface;

    /**
     * @return string[]
     */
    public function getModules(): array;

    /**
     * @return int
     */
    public function getCounter(): int;

    /**
     * @return PhpExceptionModelInterface
     */
    public function incrementCounter(): PhpExceptionModelInterface;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $value
     *
     * @return PhpExceptionModelInterface
     */
    public function setMessage(string $value): PhpExceptionModelInterface;

    /**
     * @param string $path
     *
     * @return PhpExceptionModelInterface
     */
    public function addPath(string $path): PhpExceptionModelInterface;

    /**
     * @return string[]
     */
    public function getPaths(): array;

    /**
     * @param string $url
     *
     * @return PhpExceptionModelInterface
     */
    public function addUrl(string $url): PhpExceptionModelInterface;

    /**
     * @return string[]
     */
    public function getUrls(): array;

    /**
     * @return string
     */
    public function getTrace(): string;

    /**
     * @param string $formattedTrace
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface
     */
    public function setTrace(string $formattedTrace): PhpExceptionModelInterface;

    /**
     * @param \DateTimeInterface $time
     *
     * @return PhpExceptionModelInterface
     */
    public function setCreatedAt(\DateTimeInterface $time): PhpExceptionModelInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTimeInterface $time
     *
     * @return PhpExceptionModelInterface
     */
    public function setLastSeenAt(\DateTimeInterface $time): PhpExceptionModelInterface;

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTimeImmutable
     */
    public function getLastSeenAt(): \DateTimeImmutable;

    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTimeInterface $time
     *
     * @return PhpExceptionModelInterface
     */
    public function setLastNotifiedAt(\DateTimeInterface $time): PhpExceptionModelInterface;

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTimeImmutable|NULL
     */
    public function getLastNotifiedAt(): ?\DateTimeImmutable;

    /**
     * Mark exception as new (these exceptions require developer attention)
     *
     * @param UserInterface $user
     *
     * @return PhpExceptionModelInterface
     */
    public function markAsNew(?UserInterface $user): PhpExceptionModelInterface;

    /**
     * Mark exception as repeated (it was resolved earlier but repeated now)
     *
     * @param UserInterface $user
     *
     * @return PhpExceptionModelInterface
     */
    public function markAsRepeated(?UserInterface $user): PhpExceptionModelInterface;

    /**
     * Mark exception as resolved
     *
     * @param UserInterface $user
     *
     * @return PhpExceptionModelInterface
     */
    public function markAsResolvedBy(UserInterface $user): PhpExceptionModelInterface;

    /**
     * Mark exception as ignored
     *
     * @param UserInterface $user
     *
     * @return PhpExceptionModelInterface
     */
    public function markAsIgnoredBy(UserInterface $user): PhpExceptionModelInterface;

    /**
     * Returns TRUE if current exception is in 'new' state
     *
     * @return bool
     */
    public function isNew(): bool;

    /**
     * Returns TRUE if exception was resolved
     *
     * @return bool
     */
    public function isResolved(): bool;

    /**
     * Returns TRUE if current exception is in 'repeat' state
     *
     * @return bool
     */
    public function isRepeated(): bool;

    /**
     * Returns TRUE if current exception is in 'ignored' state
     *
     * @return bool
     */
    public function isIgnored(): bool;

    /**
     * Returns user which had resolved this exception
     *
     * @return UserInterface|null
     */
    public function getResolvedByUserID(): ?string;

    /**
     * @return PhpExceptionHistoryModelInterface[]
     */
    public function getHistoricalRecords(): array;

    /**
     * Marks current exception instance as "notification required" = 1
     */
    public function notificationRequired(): void;

    /**
     * Marks current exception instance as "notification required" = 0
     */
    public function wasNotified(): void;

    /**
     * Returns true if someone needs to be notified about current exception instance
     *
     * @return bool
     */
    public function isNotificationRequired(): bool;
}
