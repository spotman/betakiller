<?php

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleHelper;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleOptionCollection;
use BetaKiller\Console\ConsoleOptionCollectionInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Env\AppEnvInterface;
use Minion_CLI;

abstract class AbstractTask implements ConsoleTaskInterface
{
    public function defineCommonOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder
                ->string(AppEnvInterface::CLI_OPTION_STAGE)
                ->optional(AppEnvInterface::MODE_DEVELOPMENT)
                ->label('Env stage'),

            $builder
                ->string(AppEnvInterface::CLI_OPTION_LOG_LEVEL)
                ->optional()
                ->label('Log level'),

            $builder
                ->bool(AppEnvInterface::CLI_OPTION_DEBUG)
                ->optional()
                ->label('Force debug'),

            $builder
                ->string(AppEnvInterface::CLI_OPTION_USER)
                ->optional()
                ->label('Run as User'),
        ];
    }

    public function getDefinedOptions(ConsoleOptionBuilderInterface $builder): ConsoleOptionCollectionInterface
    {
        return new ConsoleOptionCollection(array_merge($this->defineCommonOptions($builder), $this->defineOptions($builder)));
    }

    /**
     * Gets the task name for the task
     *
     * @return string
     */
    public function __toString()
    {
        static $taskName;

        if (!$taskName) {
            $taskName = ConsoleHelper::convert_class_to_task($this);
        }

        return $taskName;
    }

    /**
     * Get user input from CLI
     *
     * @param string $message
     * @param array  $options
     *
     * @return string
     * @deprecated
     */
    protected function read(string $message, array $options = null): string
    {
        return Minion_CLI::read($message, $options);
    }

    /**
     * @param string $message
     *
     * @return bool
     * @deprecated
     */
    protected function confirm(string $message): bool
    {
        return $this->read($message, ['y', 'n']) === 'y';
    }

    /**
     * Get password user input from CLI
     *
     * @param string $message
     *
     * @return string
     * @deprecated
     */
    protected function password(string $message): string
    {
        return Minion_CLI::password($message);
    }
}
