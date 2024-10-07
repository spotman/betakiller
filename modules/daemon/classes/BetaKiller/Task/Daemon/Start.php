<?php

declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskLocatorInterface;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

final class Start extends AbstractDaemonCommandTask
{
    private const ARG_IGNORE_RUNNING = 'ignore-running';

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
     * @param \BetaKiller\Console\ConsoleTaskLocatorInterface $taskLocator
     * @param \BetaKiller\Daemon\DaemonLockFactory            $lockFactory
     * @param \BetaKiller\Env\AppEnvInterface                 $appEnv
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(
        private ConsoleTaskLocatorInterface $taskLocator,
        DaemonLockFactory $lockFactory,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ) {
        $this->appEnv = $appEnv;
        $this->logger = $logger;

        parent::__construct($lockFactory);
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return array_merge(parent::defineOptions($builder), [
            $builder->bool(self::ARG_IGNORE_RUNNING),
        ]);
    }

    protected function proceedCommand(string $daemonName, LockInterface $lock, ConsoleInputInterface $params): void
    {
        // Check lock file exists and points to a valid pid
        if ($lock->isValid()) {
            if ($params->getBool(self::ARG_IGNORE_RUNNING)) {
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

        $cmd = $this->taskLocator->getTaskCmd('daemon:runner', [
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
