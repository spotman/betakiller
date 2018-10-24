<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use Graze\ParallelProcess\Display\Table;
use Graze\ParallelProcess\Event\RunEvent;
use Graze\ParallelProcess\Monitor\PoolLogger;
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

    public const RETRY_LIMIT = 3;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Supervisor constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        ConfigProviderInterface $config,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        $this->isHuman = $this->appEnv->isHuman();

        $this->pool = new Pool();

        foreach ($this->getDefinedDaemons() as $codename) {
            $this->addProcess($codename);
        }

        if ($this->appEnv->isDebugEnabled()) {
            $this->addProcess(SleepDaemon::CODENAME);
            $this->addProcess(FailingDaemon::CODENAME);
        }

        $verbosity = $this->appEnv->isDebugEnabled()
            ? ConsoleOutput::VERBOSITY_DEBUG
            : ConsoleOutput::VERBOSITY_VERY_VERBOSE;

        if ($this->isHuman) {
            $output = new ConsoleOutput($verbosity);
            // $table->run() throws unwanted exceptions when process dies
            new Table($output, $this->pool);
        } else {
            $monitor = new PoolLogger($this->logger);
            $monitor->monitor($this->pool);
        }

        $this->pool->run();
    }

    public function restart(): void
    {
        $runningNames = [];

        /** @var ProcessRun $run */
        foreach ($this->pool->getRunning() as $run) {
            if ($run->isRunning()) {
                $runningNames[] = $this->getNameFromRun($run);

                // Send restart signal to the daemon
                $run->getProcess()->signal(DaemonInterface::RESTART_SIGNAL);
            }
        }

        // TODO Этот массив всегда пустой, разобраться почему
        $stopped = array_diff($this->getDefinedDaemons(), $runningNames);

        $this->logger->debug('Restarting stopped daemons ":names"', [
            ':names' => \implode('", "', $stopped),
        ]);

        foreach ($stopped as $name) {
            $this->addProcess($name)->start();
        }
    }

    public function stop(): void
    {
        $this->logDebug('Shutting down daemons');

        foreach ($this->pool->getRunning() as $run) {
            if (!$run instanceof ProcessRun) {
                throw new \LogicException('Pool must consist of ProcessRun instances only');
            }

            $process = $run->getProcess();
            $this->logDebug('Sending signal to ":name" daemon with PID = :pid', [
                ':pid'  => $process->getPid(),
                ':name' => $this->getNameFromRun($run),
            ]);

            $process->stop(3);
        }

        $this->logDebug('All daemons are stopped, supervisor is shutting down');
    }

    private function getDefinedDaemons(): array
    {
        return \array_unique((array)$this->config->load(['daemons']));
    }

    private function addProcess(string $codename): ProcessRun
    {
        $cmd = AbstractTask::getTaskCmd($this->appEnv, 'daemon:run', [
            'name' => $codename,
        ]);

        $process = new Process($cmd);

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

            if (!$run->isSuccessful() && $this->checkRetryLimitExceeded($name)) {
                $this->logDebug('Daemon ":name" had failed :times times and was stopped', [
                    ':name'  => $name,
                    ':times' => self::RETRY_LIMIT,
                ]);

                return;
            }

            $this->logDebug('Restarting ":name" daemon', [
                ':name' => $name,
            ]);

            // Restart
            $this->addProcess($name)->start();
        });

        $this->pool->add($run);

        return $run;
    }

    private function checkRetryLimitExceeded(string $name): bool
    {
        $count = 0;

        /** @var ProcessRun $run */
        foreach ($this->pool->getIterator() as $run) {
            if ($this->getNameFromRun($run) === $name && !$run->isSuccessful()) {
                $count++;
            }
        }

        return $count >= self::RETRY_LIMIT;
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
            $this->logger->debug($message, $variables);
        }
    }
}
