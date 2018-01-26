<?php
namespace BetaKiller\Task;

abstract class AbstractTask extends \Minion_Task
{
    public const CLI_USER_NAME = 'minion';

    protected function getOption(string $key): ?string
    {
        return $this->_options[$key] ?? null;
    }
}
