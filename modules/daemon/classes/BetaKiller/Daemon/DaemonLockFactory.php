<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\ProcessLock\LockFactory;
use BetaKiller\ProcessLock\LockInterface;

final class DaemonLockFactory
{
    private LockFactory $lockFactory;

    /**
     * DaemonLockFactory constructor.
     *
     * @param \BetaKiller\ProcessLock\LockFactory $lockFactory
     */
    public function __construct(LockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;
    }

    public function create(string $codename): LockInterface
    {
        return $this->lockFactory->create('daemon', $codename);
    }
}
