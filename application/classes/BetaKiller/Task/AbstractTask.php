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
        $commonOptions = [
            'debug' => false,
            'stage' => 'development',
            'user'  => null,
        ];

        $this->_options = array_merge($commonOptions, $this->_options, $this->defineOptions());

        parent::__construct();
    }

    abstract public function run(): void;

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
        $value    = $this->_options[$key] ?? null;

        if ($value === null && $required) {
            throw new TaskException('Option :name is required', [':name' => $key]);
        }

        return $value;
    }

    protected function _execute(array $params)
    {
        $this->run();
    }

    /**
     * @param      $text
     * @param null $color
     *
     * @return $this
     * @deprecated Use logger instead
     */
    protected function write($text, $color = null): self
    {
        if ($color) {
            $text = $this->colorize($text, $color);
        }

        Minion_CLI::write($text);

        return $this;
    }

    /**
     * @param        $text
     * @param bool   $eol
     * @param string $color
     *
     * @return $this
     * @deprecated Use logger instead
     */
    protected function writeReplace($text, ?bool $eol, $color = null): self
    {
        if ($color) {
            $text = $this->colorize($text, $color);
        }

        Minion_CLI::write_replace($text, $eol ?? false);

        return $this;
    }

    private function colorize($text, $fore, $back = null): string
    {
        return Minion_CLI::color($text, $fore, $back);
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
     * @param $message
     *
     * @return string
     */
    protected function password($message): string
    {
        return Minion_CLI::password($message);
    }
}
