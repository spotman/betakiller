<?php
namespace BetaKiller\Task;

abstract class AbstractTask extends \Minion_Task
{
    public const CLI_USER_NAME = 'minion';

    public function __construct()
    {
        $commonOptions = [
            'debug' => false,
            'stage' => 'development',
            'user'  => null,
        ];

        $this->_options = array_merge($commonOptions, $this->_options, $this->defineOptions());

        parent::__construct();
    }

    protected function defineOptions(): array
    {
        return [];
    }

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

    abstract public function run(): void;

    protected function _execute(array $params)
    {
        $this->run();
    }
}
