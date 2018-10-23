<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use Graze\ParallelProcess\Display\Table;
use Graze\ParallelProcess\Monitor\PoolLogger;
use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\ProcessRun;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;

class SupervisorDaemon implements DaemonInterface
{
    public const CODENAME = 'Supervisor';

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

    public function start(): void
    {
        $this->isHuman = $this->appEnv->isHuman();

        $this->pool = new Pool();

        foreach (\array_unique((array)$this->config->load(['daemons'])) as $codename) {
            $this->addProcess($codename);
        }

        if ($this->appEnv->isDebugEnabled()) {
            $this->addProcess(SleepDaemon::CODENAME);
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

    public function stop(): void
    {
        foreach ($this->pool->getRunning() as $run) {
            if ($run instanceof ProcessRun) {
                $process = $run->getProcess();
                $process->stop(5);
            }
        }

        while ($this->pool->isRunning()) {
            \usleep(100000);
        }
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

//        $run->addListener(RunEvent::FAILED, function (RunEvent $runEvent) {
//            $name = $runEvent->getRun()->getTags()['name'];
//
//            $time = \microtime(true);
//
//            // Tiny delay for 3 seconds
//            while (\microtime(true) < $time + 3) {
//                usleep(10000);
//            }
//
//            // Restart
//            $this->addProcess($name)->start();
//        });

        $this->pool->add($run);

        return $run;
    }
}
