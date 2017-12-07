<?php

use BetaKiller\Task\AbstractTask;

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

    protected function _execute(array $params): void
    {
        $sitePath = $this->multiSite->getSitePath();

        $cronFile = $sitePath.DIRECTORY_SEPARATOR.'crontab';
        $records = \file($cronFile);

        if (!$records) {
            return;
        }

        foreach ($records as $record) {
            list($expression, $task) = explode(' => ', $record);

            // Cleanup spaces and tabs
            $expression = trim($expression);
            $task = trim($task);

            $cron = \Cron\CronExpression::factory($expression);

            $this->logger->debug('Checking is due :task', [':task' => $task]);

            if ($cron->isDue()) {
                // TODO Enqueue task if not queued
                $this->runTask($task);
            }
        }

        // TODO Get first queued task and run it
    }

    private function runTask(string $taskString)
    {
        $taskPrefix = '--task=';

        // Add missing prefix
        if (strpos($taskString, $taskPrefix) === false) {
            $taskString = $taskPrefix.$taskString;
        }

        $options = $this->parseTaskOptions($taskString);

        $stage = $this->env->getModeName();

        // Add stage if not defined
        if (!isset($options['stage'])) {
            $options['stage'] = $stage;
        }

        // Ensure that target stage is reached
        if ($options['stage'] !== $stage) {
            $this->logger->debug('Skipping task :name for stage :stage', [
                ':name' => $options['task'],
                ':stage' => $options['stage'],
            ]);
            return;
        }

        $sitePath = $this->multiSite->getSitePath();
        $indexPath = $sitePath.DIRECTORY_SEPARATOR.'public';

        $php = 'php';

        $cmd = "cd $indexPath && $php index.php";

        foreach ($options as $optionName => $optionValue) {
            $cmd .= ' --'.$optionName.'='.$optionValue;
        }

        $this->logger->info($cmd);

        echo shell_exec($cmd);
    }

    private function parseTaskOptions(string $taskArgs): array
    {
        // Allow any option
        $getOpt = new \GetOpt\GetOpt(null, [\GetOpt\GetOpt::SETTING_STRICT_OPTIONS => false]);

        $getOpt->process($taskArgs);

        return $getOpt->getOptions();
    }
}
