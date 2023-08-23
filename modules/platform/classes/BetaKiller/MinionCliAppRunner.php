<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Task\TaskFactory;

final class MinionCliAppRunner implements CliAppRunnerInterface
{
    /**
     * @var \BetaKiller\Task\TaskFactory
     */
    private TaskFactory $taskFactory;

    public function __construct(TaskFactory $taskFactory)
    {
        $this->taskFactory = $taskFactory;
    }

    public function run()
    {
        if (!class_exists(\Minion_Task::class)) {
            echo 'Please enable the Minion module for CLI support.';

            return;
        }

        \Minion_Task::factory(\Minion_CLI::options(), $this->taskFactory)->execute();
    }
}
