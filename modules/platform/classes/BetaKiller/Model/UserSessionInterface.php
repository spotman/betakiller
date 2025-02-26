<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use DateInterval;
use DateTimeImmutable;

interface UserSessionInterface extends AbstractEntityInterface
{
    public function setToken(string $value): UserSessionInterface;

    public function getToken(): string;

    public function getCreatedAt(): DateTimeImmutable;

    public function setCreatedAt(DateTimeImmutable $value): UserSessionInterface;

    public function getLastActiveAt(): DateTimeImmutable;

    public function setLastActiveAt(DateTimeImmutable $value): UserSessionInterface;

    public function setUser(UserInterface $user): UserSessionInterface;

    public function setUserID(string $id): UserSessionInterface;

    public function hasUser(): bool;

    public function getUser(): UserInterface;

    public function isExpiredIn(DateInterval $interval): bool;

    public function getContents(): string;

    public function setContents(string $value): UserSessionInterface;

    public function markAsRegenerated(): void;

    /**
     * @return bool
     */
    public function isRegenerated(): bool;
}
