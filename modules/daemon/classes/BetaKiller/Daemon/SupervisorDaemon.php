<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Daemon\Supervisor\DaemonController;
use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class SupervisorDaemon extends AbstractDaemon
{
    public const CODENAME = 'Supervisor';

    public const SIGNAL_RELOAD  = \SIGUSR1;
    public const SIGNAL_RESTART = \SIGUSR2;

    /**
     * @var \BetaKiller\Daemon\Supervisor\DaemonController
     */
    private DaemonController $controller;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Supervisor constructor.
     *
     * @param \BetaKiller\Daemon\Supervisor\DaemonController $controller
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        DaemonController $controller,
        LoggerInterface $logger
    ) {
        $this->logger     = $logger;
        $this->controller = $controller;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Reload signal => hot restart
        $loop->addSignal(self::SIGNAL_RELOAD, function () {
            try {
                $this->controller->restartStopped();
            } catch (\Throwable $e) {
                LoggerHelper::logRawException($this->logger, $e);
            }
        });

        // Restart signal => hot restart
        $loop->addSignal(self::SIGNAL_RESTART, function () {
            try {
                $this->controller->restartRunning();
            } catch (\Throwable $e) {
                LoggerHelper::logRawException($this->logger, $e);
            }
        });

        $this->controller->bindSystemCounters();

        // Do not wait and allow Runner to start the loop (run loop-based event handlers)
        $this->controller->startAll()->done(function () {
            $this->logger->info('Supervisor is started');
        });

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        // Stop all system timers
        $this->controller->removeSystemCounters();

        // Wait for all sub-processes
        $stopPromise = $this->controller->stopAll();

        $stopPromise->done(function () {
            $this->logger->info('Supervisor is shutting down');
        });

        return $stopPromise;
    }
}
