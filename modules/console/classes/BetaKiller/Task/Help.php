<?php

namespace BetaKiller\Task;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Console\ConsoleHelper;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\View\ViewFactoryInterface;
use Kohana;

/**
 * Help task to display general instructions and list all tasks
 */
class Help extends AbstractTask
{
    public function __construct(private readonly AppConfigInterface $appConfig, private readonly ViewFactoryInterface $viewFactory)
    {
    }

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
     */
    public function run(ConsoleInputInterface $params): void
    {
        $namespaces  = [
            'BetaKiller',
            $this->appConfig->getNamespace(),
        ];

        $tasks = [];

        foreach ($namespaces as $ns) {
            $tasks[] = ConsoleHelper::_compile_task_list(Kohana::list_files(sprintf('classes/%s/Task', $ns)));
        }

        $view = $this->viewFactory->create('console/help/listing');

        $view
            ->set('tasks', array_merge(...$tasks))
            ->set('script', $_SERVER['argv'][0]);

        echo $view->render();
    }
}
