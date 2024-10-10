<?php

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleOptionCollection;
use BetaKiller\Console\ConsoleOptionCollectionInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Env\AppEnvInterface;

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
}
