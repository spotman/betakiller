<?php
namespace BetaKiller\Task;

abstract class AbstractTask extends \Minion_Task
{
    public const CLI_USER_NAME = 'minion';

    /**
     * @param string    $key
     * @param bool|null $required
     *
     * @return null|string
     * @throws \BetaKiller\Task\TaskException
     */
    protected function getOption(string $key, ?bool $required = null): ?string
    {
        $required = $required ?? true;
        $value = $this->_options[$key] ?? null;

        if ($value === null && $required) {
            throw new TaskException('Option :name is required', [':name' => $key]);
        }

        return $value;
    }
}
