<?php

use BetaKiller\Console\ConsoleHelper;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;

/**
 * Help task to display general instructons and list all tasks
 *
 * @package        Kohana
 * @category       Helpers
 * @author         Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license        http://kohanaframework.org/license
 */
class Task_Help extends AbstractTask
{
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    /**
     * Generates a help list for all tasks
     *
     * @param \BetaKiller\Console\ConsoleInputInterface $params
     *
     * @return void
     * @throws \View_Exception
     */
    public function run(ConsoleInputInterface $params): void
    {
        $tasks = ConsoleHelper::_compile_task_list(Kohana::list_files('classes/Task'));

        $view = View::factory('minion/help/list');

        $view->set('tasks', $tasks);

        echo $view->render();
    }
}
