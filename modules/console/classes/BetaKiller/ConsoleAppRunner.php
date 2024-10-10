<?php

declare(strict_types=1);

namespace BetaKiller;

use Beberlei\Metrics\Collector\Collector;
use BetaKiller\Console\ConsoleHelper;
use BetaKiller\Console\ConsoleInput;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskFactory;
use BetaKiller\View\ViewFactoryInterface;

final readonly class ConsoleAppRunner implements CliAppRunnerInterface
{
    public function __construct(
        private AppEnvInterface $appEnv,
        private TaskFactory $taskFactory,
        private ConsoleOptionBuilderInterface $optionBuilder,
        private ViewFactoryInterface $viewFactory,
        private Collector $metrics
    ) {
    }

    public function run(): void
    {
        if (!class_exists(AbstractTask::class)) {
            echo 'Please enable the Console module for CLI support.';

            return;
        }

        $start = microtime(true);

        // If we didn't get a valid task, generate the help
        $taskName = $this->appEnv->getCliOption('task');

        if (empty($taskName) || str_starts_with($taskName, '--')) {
            $taskName = 'help';
        }

        $requestValues = ConsoleHelper::getRequestOptions();

        $className = ConsoleHelper::convert_task_to_class_name($taskName);
        $instance  = $this->taskFactory->create($className);

        $taskOptions = $instance->getDefinedOptions($this->optionBuilder);

        // Show the help page for this task if requested
        if ($this->appEnv->hasCliOption('help')) {
            ConsoleHelper::displayTaskHelp($instance, $taskOptions, $this->viewFactory);
        } else {
            $input = ConsoleInput::createFrom($requestValues, $taskOptions);

            $instance->run($input);
        }

        $duration = (microtime(true) - $start) * 1000;

        $metricsName = str_replace(ConsoleHelper::$task_separator, '.', $taskName);

        $this->metrics->measure(sprintf('task.%s', $metricsName), $duration);
        $this->metrics->flush();
    }
}
