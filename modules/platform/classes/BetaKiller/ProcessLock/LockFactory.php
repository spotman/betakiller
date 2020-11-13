<?php
declare(strict_types=1);

namespace BetaKiller\ProcessLock;

use BetaKiller\Helper\AppEnvInterface;

final class LockFactory
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * LockFactory constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    public function create(string $namespace, string $codename): LockInterface
    {
        if (!$namespace) {
            throw new \LogicException('ProcessLock namespace is not defined');
        }

        if (!$codename) {
            throw new \LogicException('ProcessLock codename is not defined');
        }

        $fileName = implode('.', [
            // App codename and mode are already processed via AppEnv
//            $this->appEnv->getAppCodename(),
//            $this->appEnv->getModeName(),
            $codename,
            $namespace,
            'lock',
        ]);

        return new Lock($this->appEnv->getTempPath($fileName));
    }
}
