<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Task\AbstractTask;

class Status extends AbstractTask
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private $lockFactory;

    /**
     * Start constructor.
     *
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     * @param \BetaKiller\Env\AppEnvInterface      $appEnv
     */
    public function __construct(DaemonLockFactory $lockFactory, AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
        $this->lockFactory = $lockFactory;

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            'name' => null,
        ];
    }

    public function run(): void
    {
        $name = \ucfirst((string)$this->getOption('name', true));

        if (!$name) {
            throw new \LogicException('Daemon codename is not defined');
        }

        // Get lock
        $lock = $this->lockFactory->create($name);

        if ($lock->isAcquired() && $lock->isValid()) {
            echo sprintf('Daemon is running on pid %s'.PHP_EOL, $lock->getPid());
        } elseif ($lock->isAcquired()) {
            echo sprintf('Lock is acquired but daemon is stale on pid %s'.PHP_EOL, $lock->getPid());
        } else {
            echo 'Daemon is stopped'.PHP_EOL;
        }
    }
}
