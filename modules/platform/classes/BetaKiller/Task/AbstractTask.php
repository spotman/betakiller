<?php
namespace BetaKiller\Task;

use BetaKiller\Helper\AppEnvInterface;
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

    public static function getTaskCmd(
        AppEnvInterface $appEnv,
        string $taskName,
        array $params = null,
        bool $showOutput = null,
        bool $detach = null
    ): string {
        $php   = PHP_BINARY;
        $stage = $appEnv->getModeName();

        $cmd = "$php index.php --task=$taskName --stage=$stage";

        if ($params) {
            foreach ($params as $optionName => $optionValue) {
                $cmd .= ' --'.$optionName.'='.$optionValue;
            }
        }

        if (!$showOutput) {
            $fileNameArray = [
                $taskName,
                $stage,
            ];

            // Add parameters to logfile to separate logs for calls with different arguments
            foreach ($params as $optionName => $optionValue) {
                $fileNameArray[] = $optionName.'-'.$optionValue;
            }

            $logFileName = implode('.', $fileNameArray).'.log';
            $logPath = implode('/', [$appEnv->getTempPath(), $logFileName]);

            // Redirect all output to log file (logger is still usable)
            $cmd .= " >> $logPath 2>&1";
        }

        if ($detach) {
            // @see https://unix.stackexchange.com/a/30433
            // Process will become a "zombie" without "exec" call so use this function with care
            $cmd = sprintf('setsid %s < /dev/null &', $cmd);
        } else {
            // "exec" call removes shell wrapping and simplifies process signaling
            $cmd = 'exec '.$cmd;
        }

        return $cmd;
    }

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
    protected function read(string $message, array $options = null): string
    {
        return Minion_CLI::read($message, $options);
    }

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
     */
    protected function password(string $message): string
    {
        return Minion_CLI::password($message);
    }
}
