<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\LockFactory;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use Symfony\Component\Process\Process;

class Ping extends AbstractTask
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Daemon\LockFactory
     */
    private $lockFactory;

    /**
     * Start constructor.
     *
     * @param \BetaKiller\Daemon\LockFactory     $lockFactory
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(LockFactory $lockFactory, AppEnvInterface $appEnv)
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

        // Check lock file exists and points to a valid pid
        if ($lock->isAcquired()) {
            echo sprintf( 'Daemon "%s" is already running'.PHP_EOL, $name);

            return;
        }

        $cmd = self::getTaskCmd($this->appEnv, 'daemon:start', [
            'name' => $name,
        ], false, false);

        $process = Process::fromShellCommandline($cmd, $this->appEnv->getDocRootPath());

        // Execute start task
        $process
            ->disableOutput()
            ->run();
    }
}
