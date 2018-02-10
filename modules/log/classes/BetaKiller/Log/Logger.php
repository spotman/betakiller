<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnvInterface;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use MultiSite;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var \Monolog\Logger
     */
    private $monolog;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var MultiSite
     */
    private $multiSite;

    /**
     * Logger constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $env
     * @param \MultiSite                         $multiSite
     *
     * @throws \Exception
     */
    public function __construct(AppEnvInterface $env, MultiSite $multiSite)
    {
        $this->appEnv    = $env;
        $this->multiSite = $multiSite;
        $this->monolog   = $this->getMonologInstance();
    }

    /**
     * @return \Monolog\Logger
     * @throws \Exception
     */
    private function getMonologInstance(): \Monolog\Logger
    {
        $monolog = new \Monolog\Logger('default');

        $isDebugAllowed = $this->appEnv->isDebugEnabled();

        // CLI mode logging
        if ($this->appEnv->isCLI()) {
            $monolog->pushHandler(new CliHandler($isDebugAllowed));
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

        $logsLevel = $isDebugAllowed ? $monolog::DEBUG : $monolog::NOTICE;

        $monolog->pushHandler(new FingersCrossedHandler($groupHandler, $logsLevel));

        $monolog->pushProcessor(new KohanaPlaceholderProcessor());
        $monolog->pushProcessor(new MemoryPeakUsageProcessor());
        $monolog->pushProcessor(new IntrospectionProcessor($monolog::NOTICE));

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
        $this->monolog->log($level, $message, $context);
    }

    /**
     * @param \Monolog\Handler\HandlerInterface $handler
     */
    public function pushHandler(HandlerInterface $handler)
    {
        $this->monolog->pushHandler($handler);
    }
}
