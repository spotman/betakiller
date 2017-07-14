<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnv;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use MultiSite;
use PhpConsole\Connector;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var MultiSite
     */
    private $multiSite;

    /**
     * Logger constructor.
     *
     * @param \BetaKiller\Helper\AppEnv $env
     * @param MultiSite                 $multiSite
     */
    public function __construct(AppEnv $env, MultiSite $multiSite)
    {
        $this->appEnv    = $env;
        $this->multiSite = $multiSite;
        $this->logger    = $this->getMonologInstance();
    }

    protected function getMonologInstance()
    {
        $monolog = new \Monolog\Logger('default');

//        TODO PhpExceptionStorage handler
//        ErrorHandler::register($monolog);

        $logFilePath     = implode(DIRECTORY_SEPARATOR, ['logs', date('Y'), date('m'), date('d').'.log']);
        $coreLogFilePath = APPPATH.$logFilePath;
        $appLogFilePath  = $this->multiSite->getWorkingPath().DIRECTORY_SEPARATOR.$logFilePath;

        $debugAllowed = $this->appEnv->isDebugEnabled();

        $groupHandler = new WhatFailureGroupHandler([
            // Core logs
            new StreamHandler($coreLogFilePath, $monolog::DEBUG),

            // App logs
            new StreamHandler($appLogFilePath, $monolog::DEBUG),

            // TODO PhpExceptionStorage handler
        ]);

        $crossedHandler = new FingersCrossedHandler($groupHandler, $monolog::NOTICE);

        $monolog->pushHandler($crossedHandler);

        // CLI mode logging
        if (PHP_SAPI === 'cli') {
            // Disable original error messages
            ini_set('error_reporting', 'off');

            $cliFormat = "[%level_name%] %message%\n";
            $cliLevel  = $debugAllowed ? $monolog::DEBUG : $monolog::INFO;

            // TODO Color scheme and formatter from Minion_Log
            $cliHandler = new StreamHandler('php://stdout', $cliLevel);
            $cliHandler->setFormatter(new LineFormatter($cliFormat));

            $monolog->pushHandler($cliHandler);
        }

        // Enable debugging via PhpConsole for developers
        if ($debugAllowed && Connector::getInstance()->isActiveClient()) {
            $phpConsoleHandler       = new PHPConsoleHandler([
                'headersLimit'             => 2048,     // 2KB
                'detectDumpTraceAndSource' => true,     // Autodetect and append trace data to debug
                'useOwnErrorsHandler'      => true,     // Enable errors handling
                'useOwnExceptionsHandler'  => true,     // Enable exceptions handling
            ]);
            $stripExceptionFormatter = new StripExceptionFromContextFormatter();
            $phpConsoleHandler->setFormatter($stripExceptionFormatter);
            $monolog->pushHandler($phpConsoleHandler);
        }

        $monolog->pushProcessor(new MemoryPeakUsageProcessor());
        $monolog->pushProcessor(new WebProcessor());

        return $monolog;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = null): void
    {
        if ($context) {
            foreach ($context as $key => $item) {
                if (is_string($item) && strstr($key, ':') === 0) {
                    $message = strtr($message, $key, $item);
                    unset($context[$key]);
                }
            }
        }

        // Proxy to selected logger
        $this->logger->log($level, $message, $context);
    }
}
