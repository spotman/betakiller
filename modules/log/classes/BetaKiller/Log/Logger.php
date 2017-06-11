<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnv;
use Monolog\ErrorHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use MultiSite;
use PhpConsole\Connector;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

//use Monolog\Handler\ChromePHPHandler;

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

        // TODO Implement boolean flag in session named "debugEnabled" and enable logging of DEBUG level messages
        $debugAllowed = $this->appEnv->inDevelopmentMode();

        $groupHandler = new WhatFailureGroupHandler([
            // Core logs
            new StreamHandler($coreLogFilePath, $monolog::NOTICE),

            // App logs
            new StreamHandler($appLogFilePath, $monolog::NOTICE),

            // TODO PhpExceptionStorage handler
//            new FingersCrossedHandler(),
        ]);

        $monolog->pushHandler(new DeduplicationHandler($groupHandler));

        // Enable debugging via PhpConsole for developers
        if ($debugAllowed && Connector::getInstance()->isActiveClient()) {
            $phpConsoleHandler = new PHPConsoleHandler([
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
        // Proxy to selected logger
        $this->logger->log($level, $message, $context);
    }
}
