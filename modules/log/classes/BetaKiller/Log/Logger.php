<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnv;
use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;
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

//        TODO Enable monolog exception handlers after migrating from Kohana
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

        $logsLevel      = $debugAllowed ? $monolog::DEBUG : $monolog::NOTICE;
        $crossedHandler = new FingersCrossedHandler($groupHandler, $logsLevel);

        $monolog->pushHandler($crossedHandler);

        // CLI mode logging
        if (PHP_SAPI === 'cli') {
            // Disable original error messages
            ini_set('error_reporting', 'off');

            // Color scheme and formatter
            $cliLevel     = $debugAllowed ? $monolog::DEBUG : $monolog::INFO;
            $cliHandler   = new StreamHandler('php://stdout', $cliLevel);
            $cliFormatter = new ColoredLineFormatter(new DefaultScheme(), "%message%\n");
            $cliHandler->setFormatter($cliFormatter);

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

        $monolog->pushProcessor(new KohanaPlaceholderProcessor());
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
        // Proxy to selected logger
        $this->logger->log($level, $message, $context);
    }
}
