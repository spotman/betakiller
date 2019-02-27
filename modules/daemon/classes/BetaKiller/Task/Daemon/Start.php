<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\LockFactory;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class Start extends AbstractTask
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Start constructor.
     *
     * @param \BetaKiller\Daemon\LockFactory     $lockFactory
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(LockFactory $lockFactory, AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv      = $appEnv;
        $this->lockFactory = $lockFactory;
        $this->logger      = $logger;

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
            'name'           => null,
            'ignore-running' => false,
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
        if ($lock->isValid()) {
            if ($this->getOption('ignore-running')) {
                return;
            }

            throw new TaskException('Daemon ":name" is already running', [
                ':name' => $name,
            ]);
        }

        // Cleanup stale lock
        if ($lock->isAcquired()) {
            $lock->release();
        }

        $cmd = self::getTaskCmd($this->appEnv, 'daemon:runner', [
            'name' => $name,
        ], false, true);

        $docRoot = $this->appEnv->getDocRootPath();

        $this->logger->debug('Starting ":name" daemon with :cmd', [
            ':name' => $name,
            ':cmd'  => $cmd,
        ]);

        // Start daemon runner and detach it
//        pclose(popen("cd $docRoot && $cmd", 'r'));

        Process::fromShellCommandline($cmd, $docRoot)
            ->setTimeout(null)
            ->disableOutput()
            ->setIdleTimeout(null)
            ->start();

        $this->logger->debug('Waiting for lock to be acquired by ":name" daemon', [
            ':name' => $name,
            ':cmd'  => $cmd,
        ]);

        // Ensure daemon was started
        $lock->waitForAcquire(Runner::START_TIMEOUT + 1);

        $this->logger->debug('Daemon ":name" was successfully started', [
            ':name' => $name,
        ]);
    }
}
