<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Daemon\Supervisor\DaemonController;
use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Throwable;
use function React\Promise\resolve;
use const SIGUSR1;
use const SIGUSR2;

final class SupervisorDaemon extends AbstractDaemon
{
    public const CODENAME = 'Supervisor';

    public const SIGNAL_RELOAD  = SIGUSR1;
    public const SIGNAL_RESTART = SIGUSR2;

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
        LoggerInterface  $logger
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
            } catch (Throwable $e) {
                LoggerHelper::logRawException($this->logger, $e);
            }
        });

        // Restart signal => hot restart
        $loop->addSignal(self::SIGNAL_RESTART, function () {
            try {
                $this->controller->restartRunning();
            } catch (Throwable $e) {
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

        // Prevent failed promise exception
        $stopPromise->then(
            fn() => $this->logger->info('Supervisor is shutting down'),
            fn() => $this->logger->warning('Some daemons have not been stopped, kill forced'),
        );

        return $stopPromise;
    }

    /**
     * @inheritDoc
     */
    public function isRestartOnFsChangesAllowed(): bool
    {
        // Keep Supervisor running, restart manually if required
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isShutdownOnSigTermAllowed(): bool
    {
        // Supervisor will react to system reboot and shut down all daemons
        return true;
    }

    public function getStartupTimeout(): int
    {
        // Wait for all child processes to be started
        return AbstractDaemon::STARTUP_TIMEOUT * 2;
    }

    public function getShutdownTimeout(): int
    {
        // Wait for all child processes to be terminated
        return AbstractDaemon::SHUTDOWN_TIMEOUT * 2;
    }
}
