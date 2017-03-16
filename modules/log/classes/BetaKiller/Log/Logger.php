<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\CurrentUserTrait;
use Monolog\ErrorHandler;
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

        $logFileName = date('Y'.DIRECTORY_SEPARATOR.'m'.DIRECTORY_SEPARATOR.'d').'.log';
        $coreLogFilePath = APPPATH.'logs'.DIRECTORY_SEPARATOR.$logFileName;
        $appLogFilePath = \MultiSite::instance()->site_path().DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.$logFileName;

        $groupHandler = new WhatFailureGroupHandler([
            // Core logs
            new StreamHandler($coreLogFilePath, $monolog::NOTICE),

            // App logs
            new StreamHandler($appLogFilePath, $monolog::NOTICE),

            // TODO CLI

            // TODO SQLite storage
//            new FingersCrossedHandler()
        ]);

//        $monolog->pushHandler($groupHandler);
        $monolog->pushHandler(new DeduplicationHandler($groupHandler));

        $user = $this->current_user(true);

        // Enable debugging via PhpConsole for developers
        if ($user && $user->is_developer()) {
            $monolog->pushHandler(new PHPConsoleHandler());
        }

//        $monolog->pushProcessor(new IntrospectionProcessor($monolog::NOTICE, [], 0));
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
