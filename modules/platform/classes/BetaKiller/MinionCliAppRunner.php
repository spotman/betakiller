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
use InvalidArgumentException;

final readonly class MinionCliAppRunner implements CliAppRunnerInterface
{
    public function __construct(
        private AppEnvInterface $appEnv,
        private TaskFactory $taskFactory,
        private ConsoleOptionBuilderInterface $optionBuilder,
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
        $taskName = $this->appEnv->getCliOption('task') ?? 'help';

        $taskName = trim($taskName);

        if (empty($taskName)) {
            throw new InvalidArgumentException('Missing "task" option');
        }

        $requestValues = ConsoleHelper::getRequestOptions();

        $className = ConsoleHelper::convert_task_to_class_name($taskName);
        $instance = $this->taskFactory->create($className);

        // Show the help page for this task if requested
        if ($this->appEnv->hasCliOption('help')) {
            ConsoleHelper::displayHelp($instance);
        } else {
            $taskOptions = $instance->getDefinedOptions($this->optionBuilder);

            $input = ConsoleInput::createFrom($requestValues, $taskOptions);

            $instance->run($input);
        }

        $duration = (microtime(true) - $start) * 1000;

        $metricsName = str_replace(ConsoleHelper::$task_separator, '.', $taskName);

        $this->metrics->measure(sprintf('task.%s', $metricsName), $duration);
        $this->metrics->flush();
    }
}
