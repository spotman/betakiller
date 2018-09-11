<?php
namespace BetaKiller\Task;

use Minion_CLI;
use Minion_Task;

abstract class AbstractTask extends Minion_Task
{
    public const CLI_USER_NAME = 'minion';
    public const COLOR_RED     = 'red';
    public const COLOR_GREEN   = 'green';

    public function __construct()
    {
        $this->_options = array_merge(self::defineCommonOptions(), $this->_options, $this->defineOptions());

        parent::__construct();
    }

    public static function defineCommonOptions(): array
    {
        return [
            'debug' => false,
            'stage' => 'development',
            'user'  => null,
        ];
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    abstract public function defineOptions(): array;

    abstract public function run(): void;

    /**
     * @param string    $key
     * @param bool|null $required
     *
     * @return null|mixed
     * @throws \BetaKiller\Task\TaskException
     */
    protected function getOption(string $key, ?bool $required = null)
    {
        $required = $required ?? true;
        $value    = $this->_options[$key] ?? null;

        if ($value === null && $required) {
            throw new TaskException('Option :name is required', [':name' => $key]);
        }

        return $value;
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    protected function _execute(array $params): void
    {
        $this->run();
    }

    /**
     * Get user input from CLI
     *
     * @param string $message
     * @param array  $options
     *
     * @return string
     */
    protected function read($message, array $options = null): string
    {
        return Minion_CLI::read($message, $options);
    }

    /**
     * Get password user input from CLI
     *
     * @param string $message
     *
     * @return string
     */
    protected function password(string $message): string
    {
        return Minion_CLI::password($message);
    }
}
