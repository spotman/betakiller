<?php

use BetaKiller\Cron\CronException;
use BetaKiller\Cron\Task;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Cron\CronExpression;
use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class Task_Cron extends AbstractTask
{
    /**
     * @Inject
     * @var \MultiSite
     */
    private $multiSite;

    /**
     * @Inject
     * @var \BetaKiller\Helper\AppEnv
     */
    private $env;

    /**
     * @Inject
     * @var \BetaKiller\Cron\TaskQueue
     */
    private $queue;

    /**
     * @var string
     */
    private $currentStage;

    /**
     * @var bool
     */
    private $isHuman;

    protected function defineOptions(): array
    {
        return [
            'human' => false,
        ];
    }

    /**
     * @param array $params
     *
     * @throws \Exception
     * @throws \BetaKiller\Task\TaskException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    protected function _execute(array $params): void
    {
        $this->isHuman      = ($params['human'] !== false);
        $this->currentStage = $this->env->getModeName();

        // Get all due tasks and enqueue them (long-running task would not affect next tasks due check)
        $this->enqueueDueTasks();

        // Get all queued tasks and run them one by one
        $this->runQueuedTasks();
    }

    /**
     * @throws \BetaKiller\Task\TaskException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function enqueueDueTasks(): void
    {
        $sitePath = $this->multiSite->getSitePath();

        $cronFile = $sitePath.DIRECTORY_SEPARATOR.'crontab.yml';

        /** @var array[] $records */
        $records = Yaml::parseFile($cronFile, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        if (!$records) {
            throw new TaskException('Missing crontab.yml');
        }

        foreach ($records as $name => $data) {
            $expr         = $data['at'] ?? null;
            $params       = $data['params'] ?? null;
            $taskStage    = $data['stage'] ?? null;

            if (!$expr) {
                throw new TaskException('Missing "at" key value in [:name] task', [':name' => $name]);
            }

            // Ensure that target stage is reached
            if ($taskStage && $taskStage !== $this->currentStage) {
                $this->logger->debug('Skipping task [:name] for stage :stage', [
                    ':name'  => $name,
                    ':stage' => $taskStage,
                ]);

                continue;
            }

            $cron = CronExpression::factory($expr);

            if (!$cron->isDue()) {
                $this->logger->debug('Task [:task] is not due, skipping', [':task' => $name]);
                continue;
            }

            $this->logger->debug('Task [:task] is due', [':task' => $name]);

            $task = new Task($name, $params);

            // Skip task if already queued
            if ($this->queue->isQueued($task)) {
                $this->logger->warning('Task [:task] is already queued, skipping', [':task' => $name]);
                continue;
            }

            // Enqueue task
            $this->queue->enqueue($task);

            $this->logger->debug('Task [:task] was queued', [':task' => $name]);
        }
    }

    /**
     * @throws \Exception
     */
    private function runQueuedTasks(): void
    {
        $pool = new Pool();
        $pool->setMaxSimultaneous(3);

        $pool->setOnStart(function (Process $process) {
            $task = $this->getTaskByProcessFingerprint($process);

            $task->started();

            $this->logger->debug('Task [:name] is started', [':name' => $task->getName()]);
        });

        $pool->setOnSuccess(function (Process $process) {
            $task = $this->getTaskByProcessFingerprint($process);

            $task->done();
            $this->queue->dequeue($task);

            $this->logger->debug('Task [:name] is done!', [':name' => $task->getName()]);
        });

        $pool->setOnFailure(function (Process $process) {
            $task = $this->getTaskByProcessFingerprint($process);

            $task->failed();

            $nextRunTime = new DateTimeImmutable;
            $task->postpone($nextRunTime->add(new DateInterval('PT5M')));

            $this->logger->debug('Task [:name] is failed', [':name' => $task->getName()]);
        });


        $verbosity = $this->env->isDebugEnabled()
            ? ConsoleOutput::VERBOSITY_DEBUG
            : ConsoleOutput::VERBOSITY_VERY_VERBOSE;

        $output = new ConsoleOutput($verbosity);
        $table  = new Table($output, $pool);

        // Select queued tasks where start_at >= current time, limit 5
        // It allows to postpone failed tasks for 5 minutes
        foreach ($this->queue->getReadyToStart() as $task) {
            $this->logger->debug('Task [:name] is ready to start', [':name' => $task->getName()]);

            $cmd = $this->getTaskCmd($task, $this->currentStage);

            $process = new Process($cmd);

            // Store fingerprint for simpler task identification upon start
            $process->setEnv([
                'fingerprint' => $task->getFingerprint(),
            ]);

            if ($this->isHuman) {
                $table->add($process);
            } else {
                $pool->add($process);
            }
        }

        if ($this->isHuman) {
            $table->run();
        } else {
            $pool->run();
        }
    }

    /**
     * @param \Symfony\Component\Process\Process $process
     *
     * @return \BetaKiller\Cron\Task
     * @throws \BetaKiller\Cron\CronException
     */
    private function getTaskByProcessFingerprint(Process $process): Task
    {
        $env         = $process->getEnv();
        $fingerprint = $env['fingerprint'] ?? null;

        if (!$fingerprint) {
            throw new CronException('Missing process fingerprint');
        }

        return $this->queue->getByFingerprint($fingerprint);
    }

    private function getTaskCmd(Task $task, string $stage): string
    {
        $options = [
            'task'  => $task->getName(),
            'stage' => $stage,
        ];

        $params = $task->getParams();

        if ($params) {
            $options += $params;
        }

        $sitePath  = $this->multiSite->getSitePath();
        $indexPath = $sitePath.DIRECTORY_SEPARATOR.'public';

        $php = PHP_BINARY;

        $cmd = "cd $indexPath && $php index.php";

        foreach ($options as $optionName => $optionValue) {
            $cmd .= ' --'.$optionName.'='.$optionValue;
        }

        return $cmd;
    }
}
