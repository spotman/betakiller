<?php
declare(strict_types=1);

namespace BetaKiller\ProcessLock;

use BetaKiller\Exception;

final class Lock implements LockInterface
{
    private const TIMEOUT_ACQUIRE = 3;
    private const TIMEOUT_RELEASE = 3;

    /**
     * @var string
     */
    private string $path;

    /**
     * Lock constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function acquire(int $pid): bool
    {
        // If lock file exists, check if stale.  If exists and is not stale, return TRUE
        // Else, create lock file and return FALSE.

        // The @ in front of 'symlink' is to suppress the NOTICE you get if the LOCK_FILE exists
        if (@symlink('/proc/'.$pid, $this->path) !== false) {
            $this->clearCache();

            return true;
        }

        // Link already exists, check if it's stale
        if ($this->isAcquired() && !$this->isValid()) {
            $this->release();

            // Try to lock again
            return $this->acquire($pid);
        }

        return false;
    }

    public function release(): bool
    {
        if (!$this->isAcquired()) {
            throw new Exception('Can not release lock in ":path" coz it is not acquired', [
                ':path' => $this->path,
            ]);
        }

        $result = \unlink($this->path);

        $this->clearCache();

        return $result;
    }

    public function isAcquired(): bool
    {
        $this->clearCache();

        return \is_link($this->path);
    }

    public function isValid(): bool
    {
        $this->clearCache();

        return \is_link($this->path) && \is_dir($this->path);
    }

    /**
     * @param int|null $timeout
     *
     * @throws \BetaKiller\Exception
     */
    public function waitForAcquire(int $timeout = null): void
    {
        $timeout = $timeout ?? self::TIMEOUT_ACQUIRE;
        $start   = \microtime(true);
        $end     = $start + $timeout;

        // wait for daemon to be stopped
        while (\microtime(true) < $end) {
            if ($this->isAcquired()) {
                break;
            }

            \usleep(100000);
        }

        if (!$this->isAcquired()) {
            throw new Exception('Lock had not been acquired in :timeout seconds at path ":path"', [
                ':path'    => $this->path,
                ':timeout' => $timeout,
            ]);
        }
    }

    /**
     * @param int|null $timeout
     *
     * @return bool
     */
    public function waitForRelease(int $timeout = null): bool
    {
        $timeout = $timeout ?? self::TIMEOUT_RELEASE;
        $start   = \microtime(true);
        $end     = $start + $timeout;

        // wait for daemon to be stopped
        while (\microtime(true) < $end) {
            if (!$this->isAcquired()) {
                break;
            }

            \usleep(100000);
        }

        return !$this->isAcquired();
    }

    public function getPid(): int
    {
        if (!$this->isAcquired()) {
            throw new Exception('Can not get PID coz lock in ":path" is not acquired', [
                ':path' => $this->path,
            ]);
        }

        $processPath = \readlink($this->path);

        return (int)\basename($processPath);
    }

    private function clearCache(): void
    {
        \clearstatcache(true, $this->path);
    }
}
