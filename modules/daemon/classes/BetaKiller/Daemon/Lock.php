<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Exception;
use Psr\Log\LoggerInterface;

class Lock
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Lock constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function acquire(int $pid): bool
    {
        // If lock file exists, check if stale.  If exists and is not stale, return TRUE
        // Else, create lock file and return FALSE.

        // The @ in front of 'symlink' is to suppress the NOTICE you get if the LOCK_FILE exists
        if (@symlink('/proc/'.$pid, $this->path) !== false) {
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

        return \unlink($this->path);
    }

    public function isAcquired(): bool
    {
        return \is_link($this->path);
    }

    /**
     * @param int|null $timeout
     *
     * @throws \BetaKiller\Exception
     * @todo is not working
     */
    public function waitForRelease(int $timeout = null): void
    {
        $timeout = $timeout ?? 3;
        $start   = \microtime(true);
        $end     = $start + $timeout;

        // wait for daemon to be stopped
        while (\microtime(true) < $end) {
            if (!$this->isAcquired()) {
                break;
            }

            \usleep(100000);
        }

        if ($this->isAcquired()) {
            throw new Exception('Lock had not been released in :timeout seconds at path ":path"', [
                ':path'    => $this->path,
                ':timeout' => $timeout,
            ]);
        }
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

    public function isValid(): bool
    {
        return \is_link($this->path) && \is_dir($this->path);
    }
}
