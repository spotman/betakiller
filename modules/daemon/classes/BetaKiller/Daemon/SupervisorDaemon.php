<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use Graze\ParallelProcess\Display\Table;
use Graze\ParallelProcess\Event\RunEvent;
use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\ProcessRun;
use Graze\ParallelProcess\RunInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;

class SupervisorDaemon implements DaemonInterface
{
    public const CODENAME = 'Supervisor';

    public const RETRY_LIMIT   = 3;
    public const RELOAD_SIGNAL = SIGUSR1;

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var bool
     */
    private $isHuman;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var int[]
     */
    private $failureCounter = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Daemon\LockFactory
     */
    private $lockFactory;

    /**
     * Supervisor constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \BetaKiller\Daemon\LockFactory             $lockFactory
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        ConfigProviderInterface $config,
        AppEnvInterface $appEnv,
        LockFactory $lockFactory,
        LoggerInterface $logger
    ) {
        $this->config      = $config;
        $this->appEnv      = $appEnv;
        $this->logger      = $logger;
        $this->lockFactory = $lockFactory;
    }

    public function start(LoopInterface $loop): void
    {
        $this->isHuman = $this->appEnv->isHuman();

        // Reload signal => hot restart
        $loop->addSignal(self::RELOAD_SIGNAL, function () {
            $this->logDebug('Reloading supervisor');
            $this->restart();
        });

        $this->pool = new Pool();

        foreach ($this->getDefinedDaemons() as $codename) {
            $this->addProcess($codename);
        }

        $verbosity = $this->appEnv->isDebugEnabled()
            ? ConsoleOutput::VERBOSITY_DEBUG
            : ConsoleOutput::VERBOSITY_VERY_VERBOSE;

        if ($this->isHuman) {
            $output = new ConsoleOutput($verbosity);
            // $table->run() throws unwanted exceptions when process dies
            new Table($output, $this->pool);
        }

        $this->pool->run();
    }

    public function restart(): void
    {
        foreach ($this->getRunningNames() as $name) {
            $run = $this->getRunByName($name);

            // Skip processes which are restarting already (race condition with event handler)
            if (!$run) {
                continue;
            }

            $process = $run->getProcess();

            // Skip processes which are restarting already (race condition with event handler)
            if (!$process->isRunning()) {
                continue;
            }

            $lock = $this->lockFactory->create($name);

            if (!$lock->isAcquired()) {
                throw new Exception('Daemon ":name" is running but has no acquired lock', [
                    ':name' => $name,
                ]);
            }

            // Send stop signal to the daemon
            $process->stop(5);

            // Wait for lock (will be released by the daemon)
            while ($lock->isAcquired()) {
                \usleep(100000);
            }
        }

        $failed = array_keys($this->failureCounter);

        $this->logDebug('Restarting failed daemons ":names"', [
            ':names' => \implode('", "', $failed),
        ]);

        // Trying to restart failed daemons
        foreach ($failed as $name) {
            $this->addProcess($name)->start();
        }

        // Reset failed daemons
        $this->failureCounter = [];
    }

    public function stop(): void
    {
        $this->logDebug('Shutting down daemons');

        foreach ($this->pool->getRunning() as $run) {
            if (!$run instanceof ProcessRun) {
                throw new \LogicException('Pool must consist of ProcessRun instances only');
            }

            $process = $run->getProcess();
            $this->logger->info('Sending signal to ":name" daemon with PID = :pid', [
                ':pid'  => $process->getPid(),
                ':name' => $this->getNameFromRun($run),
            ]);

            $process->stop(3);
        }

        $this->logger->info('All daemons are stopped, supervisor is shutting down');
    }

    private function getDefinedDaemons(): array
    {
        return \array_unique((array)$this->config->load(['daemons']));
    }

    private function addProcess(string $codename): ProcessRun
    {
        $cmd = AbstractTask::getTaskCmd($this->appEnv, 'daemon:runner', [
            'name' => $codename,
        ]);

        $docRoot = $this->appEnv->getDocRootPath();

        $process = Process::fromShellCommandline($cmd, $docRoot);

        $process
            ->setTimeout(null)
            ->setIdleTimeout(null)
            ->disableOutput()
            ->inheritEnvironmentVariables(true);

        $run = new ProcessRun($process, [
            'name' => $codename,
        ]);

        $run->addListener(RunEvent::COMPLETED, function (RunEvent $runEvent) {
            $run  = $runEvent->getRun();
            $name = $this->getNameFromRun($run);

            if (!$run->isSuccessful()) {
                if (empty($this->failureCounter[$name])) {
                    $this->failureCounter[$name] = 1;
                }

                // Increment failed attempts counter
                $this->failureCounter[$name]++;

                if ($this->failureCounter[$name] > self::RETRY_LIMIT) {
                    // Warning for developers
                    $this->logger->alert('Daemon ":name" had failed :times times and was stopped', [
                        ':name'  => $name,
                        ':times' => self::RETRY_LIMIT,
                    ]);

                    // No further processing
                    return;
                }
            }

            $lock = $this->lockFactory->create($name);

            if ($lock->isAcquired()) {
                // Warning for developers
                $this->logger->warning('Lock for ":name" daemon had not been released by the daemon:runner task', [
                    ':name' => $name,
                ]);

                // Something went wrong on the daemon shutdown so we need to clear the lock
                $lock->release();
            }

            // Daemon exited with a regular way
            $this->logDebug('Starting ":name" daemon', [
                ':name' => $name,
            ]);

            // Restart
            $this->addProcess($name)->start();

            // Wait for lock (will be acquired by the daemon)
            while (!$lock->isAcquired()) {
                \usleep(100000);
            }
        });

        $this->pool->add($run);

        return $run;
    }

    /**
     * @return string[]
     */
    private function getRunningNames(): array
    {
        return \array_map(function (ProcessRun $run) {
            return $this->getNameFromRun($run);
        }, $this->pool->getRunning());
    }

    private function getNameFromRun(RunInterface $run): string
    {
        return $run->getTags()['name'];
    }

    private function getRunByName(string $name): ?ProcessRun
    {
        /** @var ProcessRun $run */
        foreach ($this->pool->getRunning() as $run) {
            if ($this->getNameFromRun($run) === $name) {
                return $run;
            }
        }

        return null;
    }

    private function logDebug(string $message, array $variables = null): void
    {
        if (!$this->isHuman) {
            $this->logger->debug($message, $variables ?? []);
        }
    }
}
