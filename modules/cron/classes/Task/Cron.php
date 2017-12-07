<?php

use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Cron\CronExpression;
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
     * @todo Refactoring to database
     * @var array
     */
    private $queue = [];

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Task\TaskException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    protected function _execute(array $params): void
    {
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

        $currentStage = $this->env->getModeName();

        foreach ($records as $name => $data) {
            $expr = $data['at'] ?? null;

            if (!$expr) {
                throw new TaskException('Missing "at" key value in [:name] task', [':name' => $name]);
            }

            // Add stage if not defined
            if (!isset($data['stage'])) {
                $data['stage'] = $currentStage;
            }

            // Ensure that target stage is reached
            if ($data['stage'] !== $currentStage) {
                $this->logger->debug('Skipping task [:name] for stage :stage', [
                    ':name'  => $name,
                    ':stage' => $data['stage'],
                ]);

                continue;
            }

            $cron = CronExpression::factory($expr);

            if (!$cron->isDue()) {
                $this->logger->debug('Task [:task] is not due, skipping', [':task' => $name]);
                continue;
            }

            $this->logger->debug('Task [:task] is due', [':task' => $name]);

            // Skip task if already queued
            if (isset($this->queue[$name])) {
                $this->logger->warning('Task [:task] is already queued, skipping', [':task' => $name]);
                continue;
            }

            // Enqueue task
            $this->queue[$name] = $data;

            $this->logger->debug('Task [:task] was queued', [':task' => $name]);
        }
    }

    private function runQueuedTasks(): void
    {
        if (!$this->queue) {
            $this->logger->debug('No queued tasks, exiting');

            return;
        }

        // Get every queued task and run it
        foreach ($this->queue as $name => $data) {
            $this->runTask($name, $data);
        }
    }

    private function runTask(string $name, array $params)
    {
        $sitePath  = $this->multiSite->getSitePath();
        $indexPath = $sitePath.DIRECTORY_SEPARATOR.'public';

        $php = PHP_BINARY;

        $cmd = "cd $indexPath && $php index.php";

        $options = ['task' => $name] + $params;

        foreach ($options as $optionName => $optionValue) {
            $cmd .= ' --'.$optionName.'='.$optionValue;
        }

        $this->logger->info($cmd);

        shell_exec($cmd);
    }
}
