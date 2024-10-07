<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use BetaKiller\Env\AppEnvInterface;

readonly class ConsoleTaskLocator implements ConsoleTaskLocatorInterface
{
    public function __construct(private AppEnvInterface $appEnv)
    {
    }

    public function getTaskCmd(
        string $taskName,
        array $params = null,
        bool $showOutput = null,
        bool $detach = null
    ): string {
        $php   = PHP_BINARY;
        $stage = $this->appEnv->getModeName();

        $optionStage = AppEnvInterface::CLI_OPTION_STAGE;

        $params ??= [];

        $params['task']       = $taskName;
        $params[$optionStage] = $stage;

        $cmd = "$php index.php";

        foreach ($params as $optionName => $optionValue) {
            $cmd .= ' --'.$optionName.'='.$optionValue;
        }

        if (!$showOutput) {
            $fileNameArray = [
                $taskName,
            ];

            // Add parameters to logfile to separate logs for calls with different arguments
            foreach ($params as $optionName => $optionValue) {
                $fileNameArray[] = $optionName.'-'.$optionValue;
            }

            $logFile = implode('.', $fileNameArray).'.log';
            $logPath = $this->appEnv->getTempPath($logFile);

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
}
