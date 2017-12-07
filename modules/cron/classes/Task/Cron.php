<?php

use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
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
     * @param array $params
     *
     * @throws \BetaKiller\Task\TaskException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    protected function _execute(array $params): void
    {
        $sitePath = $this->multiSite->getSitePath();

        $cronFile = $sitePath.DIRECTORY_SEPARATOR.'crontab.yml';

        /** @var array[] $records */
        $records = Yaml::parseFile($cronFile, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        if (!$records) {
            throw new TaskException('Missing crontab.yml');
        }

        foreach ($records as $name => $data) {
            $expr = $data['at'] ?? null;

            if (!$expr) {
                throw new TaskException('Missing "at" key value in [:name] task', [':name' => $name]);
            }

            $cron = \Cron\CronExpression::factory($expr);

            $this->logger->debug('Checking is due :task', [':task' => $name]);

            if ($cron->isDue()) {
                // TODO Enqueue task if not queued
                $this->runTask($name, $data);
            }
        }

        // TODO Get first queued task and run it
    }

    private function runTask(string $name, array $params)
    {
        $stage = $this->env->getModeName();

        // Add stage if not defined
        if (!isset($params['stage'])) {
            $params['stage'] = $stage;
        }

        // Ensure that target stage is reached
        if ($params['stage'] !== $stage) {
            $this->logger->debug('Skipping task :name for stage :stage', [
                ':name'  => $name,
                ':stage' => $params['stage'],
            ]);

            return;
        }

        $sitePath  = $this->multiSite->getSitePath();
        $indexPath = $sitePath.DIRECTORY_SEPARATOR.'public';

        $php = PHP_BINARY;

        $cmd = "cd $indexPath && $php index.php";

        $options = ['task' => $name] + $params;

        foreach ($options as $optionName => $optionValue) {
            $cmd .= ' --'.$optionName.'='.$optionValue;
        }

        $this->logger->info($cmd);

        echo shell_exec($cmd);
    }
}
