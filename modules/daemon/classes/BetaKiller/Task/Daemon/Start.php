<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\AbstractDaemon;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

final class Start extends AbstractDaemonCommandTask
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Start constructor.
     *
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     * @param \BetaKiller\Env\AppEnvInterface      $appEnv
     * @param \Psr\Log\LoggerInterface             $logger
     */
    public function __construct(DaemonLockFactory $lockFactory, AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;

        parent::__construct($lockFactory);
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
            'name'           => null,
            'ignore-running' => false,
        ];
    }

    protected function proceedCommand(string $daemonName, LockInterface $lock): void
    {
        // Check lock file exists and points to a valid pid
        if ($lock->isValid()) {
            if ($this->getOption('ignore-running')) {
                return;
            }

            throw new TaskException('Daemon ":name" is already started', [
                ':name' => $daemonName,
            ]);
        }

        // Cleanup stale lock
        if ($lock->isAcquired()) {
            $lock->release();
        }

        $cmd = self::getTaskCmd($this->appEnv, 'daemon:runner', [
            'name' => $daemonName,
        ], false, true);

        $docRoot = $this->appEnv->getDocRootPath();

        $this->logger->debug('Starting ":name" daemon with :cmd', [
            ':name' => $daemonName,
            ':cmd'  => $cmd,
        ]);

        // Start daemon runner and detach it
//        pclose(popen("cd $docRoot && $cmd", 'r'));

        Process::fromShellCommandline($cmd, $docRoot)
            ->setTimeout(null)
            ->disableOutput()
            ->setIdleTimeout(null)
            ->mustRun();

        $this->logger->debug('Waiting for lock to be acquired by ":name" daemon', [
            ':name' => $daemonName,
            ':cmd'  => $cmd,
        ]);

        // Ensure daemon was started
        $lock->waitForAcquire();

        $this->logger->debug('Daemon ":name" was successfully started', [
            ':name' => $daemonName,
        ]);
    }
}
