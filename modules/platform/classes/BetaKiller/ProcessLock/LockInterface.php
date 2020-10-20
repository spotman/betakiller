<?php
declare(strict_types=1);

namespace BetaKiller\ProcessLock;

interface LockInterface
{
    public function getPath(): string;

    public function acquire(int $pid): bool;

    public function release(): bool;

    public function isAcquired(): bool;

    public function isValid(): bool;

    /**
     * @param int|null $timeout
     *
     * @throws \BetaKiller\Exception
     */
    public function waitForAcquire(int $timeout = null): void;

    /**
     * @param int|null $timeout
     *
     * @return bool
     */
    public function waitForRelease(int $timeout = null): bool;

    public function getPid(): int;
}

