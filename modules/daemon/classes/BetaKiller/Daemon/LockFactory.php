<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Helper\AppEnvInterface;
use Psr\Log\LoggerInterface;

class LockFactory
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * LockFactory constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function create(string $codename): Lock
    {
        if (!$codename) {
            throw new \LogicException('Daemon codename is not defined');
        }

        $path = $this->appEnv->getTempPath().\DIRECTORY_SEPARATOR.'.'.$codename.'.daemon.lock';

        $lock = new Lock($path);
        $lock->setLogger($this->logger);

        return $lock;
    }
}
