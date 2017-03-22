<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\CurrentUserTrait;
use Monolog\ErrorHandler;
//use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class Logger implements LoggerInterface
{
    use LoggerTrait;
    use CurrentUserTrait;

    /**
     * @var LoggerInterface
     */
    protected static $_instance;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public static function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new static;
        }

        return static::$_instance;
    }

    /**
     * Logger constructor.
     */
    protected function __construct()
    {
        $this->logger = $this->getMonologInstance();
    }

    protected function getMonologInstance()
    {
        $monolog = new \Monolog\Logger('default');

//        ErrorHandler::register($monolog);

        $logFilePath = implode(DIRECTORY_SEPARATOR, ['logs', date('Y'), date('m'), date('d').'.log']);
        $coreLogFilePath = APPPATH.$logFilePath;
        $appLogFilePath = \MultiSite::instance()->site_path().DIRECTORY_SEPARATOR.$logFilePath;

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

        $user = $this->current_user(true);

        // Enable debugging via PhpConsole for developers
        if ($user && $user->is_developer()) {
            $phpConsoleHandler = new PHPConsoleHandler([
                'headersLimit'  =>  1024,  // 1KB
            ]);
            $phpConsoleHandler->setFormatter($stripExceptionFormatter);
            $monolog->pushHandler($phpConsoleHandler);
        }

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
    public function log($level, $message, array $context = [])
    {
        // Proxy to selected logger
        $this->logger->log($level, $message, $context);
    }
}
