<?php
namespace BetaKiller\Log;

use BetaKiller\Error\PhpExceptionStorageHandler;
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
use PhpConsole\Storage\File;
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
     * @var PhpExceptionStorageHandler
     */
    private $phpExceptionStorageHandler;

    /**
     * Logger constructor.
     *
     * @param \BetaKiller\Helper\AppEnv                    $env
     * @param \MultiSite                                   $multiSite
     * @param \BetaKiller\Error\PhpExceptionStorageHandler $phpExceptionHandler
     */
    public function __construct(AppEnv $env, MultiSite $multiSite, PhpExceptionStorageHandler $phpExceptionHandler)
    {
        $this->appEnv                     = $env;
        $this->multiSite                  = $multiSite;
        $this->phpExceptionStorageHandler = $phpExceptionHandler;
        $this->logger                     = $this->getMonologInstance();

        $phpExceptionHandler->setLogger($this);
    }

    protected function getMonologInstance()
    {
        $monolog = new \Monolog\Logger('default');

        $isDebugAllowed = $this->appEnv->isDebugEnabled();

        // CLI mode logging
        if (PHP_SAPI === 'cli') {
            // Color scheme and formatter
            $cliLevel     = $isDebugAllowed ? $monolog::DEBUG : $monolog::INFO;
            $cliHandler   = new StreamHandler('php://stdout', $cliLevel);
            $cliFormatter = new ColoredLineFormatter(new DefaultScheme(), "%message%\n");
            $cliHandler->setFormatter($cliFormatter);

            $monolog->pushHandler($cliHandler);
        } else {
            $monolog->pushProcessor(new WebProcessor());
        }

        $logFilePath     = implode(DIRECTORY_SEPARATOR, ['logs', date('Y'), date('m'), date('d').'.log']);
        $coreLogFilePath = APPPATH.$logFilePath;
        $appLogFilePath  = $this->multiSite->getWorkingPath().DIRECTORY_SEPARATOR.$logFilePath;

        $groupHandler = new WhatFailureGroupHandler([
            // Core logs
            new StreamHandler($coreLogFilePath, $monolog::DEBUG),

            // App logs
            new StreamHandler($appLogFilePath, $monolog::DEBUG),
        ]);

        $logsLevel      = $isDebugAllowed ? $monolog::DEBUG : $monolog::NOTICE;
        $crossedHandler = new FingersCrossedHandler($groupHandler, $logsLevel);

        $monolog->pushHandler($crossedHandler);


        // Enable debugging via PhpConsole
        if ($isDebugAllowed) {
            $phpConsoleStoragePath = \sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->appEnv->getModeName().'.phpConsole.data';

            // Can be called only before PhpConsole\Connector::getInstance() and PhpConsole\Handler::getInstance()
            Connector::setPostponeStorage(new File($phpConsoleStoragePath));

            if (Connector::getInstance()->isActiveClient()) {
                $phpConsoleHandler       = new PHPConsoleHandler([
                    'detectDumpTraceAndSource' => true,     // Autodetect and append trace data to debug
                    'useOwnErrorsHandler'      => false,    // Enable errors handling
                    'useOwnExceptionsHandler'  => false,    // Enable exceptions handling
                ]);

                $stripExceptionFormatter = new StripExceptionFromContextFormatter();
                $phpConsoleHandler->setFormatter($stripExceptionFormatter);
                $monolog->pushHandler($phpConsoleHandler);
            }
        } elseif ($this->appEnv->inProduction(true)) {
            // PhpExceptionStorage handler
            $monolog->pushHandler($this->phpExceptionStorageHandler);
        }

        $monolog->pushProcessor(new KohanaPlaceholderProcessor());
        $monolog->pushProcessor(new MemoryPeakUsageProcessor());

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
