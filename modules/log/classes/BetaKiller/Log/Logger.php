<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnv;
use BetaKiller\Model\UserInterface;
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
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * Logger constructor.
     *
     * @param \BetaKiller\Helper\AppEnv       $env
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(AppEnv $env, UserInterface $user)
    {
        $this->appEnv = $env;
        $this->user   = $user;
        $this->logger = $this->getMonologInstance();
    }

    protected function getMonologInstance()
    {
        $monolog = new \Monolog\Logger('default');

//        ErrorHandler::register($monolog);

        $logFilePath     = implode(DIRECTORY_SEPARATOR, ['logs', date('Y'), date('m'), date('d').'.log']);
        $coreLogFilePath = APPPATH.$logFilePath;
        $appLogFilePath  = MultiSite::instance()->getWorkingPath().DIRECTORY_SEPARATOR.$logFilePath;

        $stripExceptionFormatter = new StripExceptionFromContextFormatter();

        $groupHandler = new WhatFailureGroupHandler([
            // Core logs
            new StreamHandler($coreLogFilePath, $monolog::NOTICE),

            // App logs
            new StreamHandler($appLogFilePath, $monolog::NOTICE),

            // TODO PhpExceptionStorage handler
//            new FingersCrossedHandler(),
        ]);

        $monolog->pushHandler(new DeduplicationHandler($groupHandler));

        $debugAllowed = $this->appEnv->inDevelopmentMode() || $this->user->isDeveloper();

        // Enable debugging via PhpConsole for developers
        if ($debugAllowed && Connector::getInstance()->isActiveClient()) {
            $phpConsoleHandler = new PHPConsoleHandler([
                'headersLimit'             => 2048,     // 2KB
                'detectDumpTraceAndSource' => true,     // Autodetect and append trace data to debug
                'useOwnErrorsHandler'      => true,     // Enable errors handling
                'useOwnExceptionsHandler'  => true,     // Enable exceptions handling
            ]);
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
    public function log($level, $message, array $context = [])
    {
        // Proxy to selected logger
        $this->logger->log($level, $message, $context);
    }
}
