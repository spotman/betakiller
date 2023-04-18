<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Env\AppEnvInterface;
use Psr\Log\LoggerInterface;

class UnixSocketFactory
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * LockFactory constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function create(string $codename): UnixSocket
    {
        if (!$codename) {
            throw new \LogicException('Daemon codename is not defined');
        }

        $fileName = implode('.', [
            $this->appEnv->getAppCodename(),
            $this->appEnv->getModeName(),
            $codename,
            'daemon.sock',
        ]);

        $path = $this->appEnv->getStoragePath($fileName);

        $socket = new UnixSocket($path);
        $socket->setLogger($this->logger);

        return $socket;
    }
}
