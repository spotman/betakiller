<?php

declare(strict_types=1);

namespace BetaKiller\Daemon\Supervisor;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Console\ConsoleTaskLocatorInterface;
use BetaKiller\Daemon\AbstractDaemon;
use BetaKiller\Daemon\DaemonException;
use BetaKiller\Daemon\DaemonInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Task\Daemon\Runner;
use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Validator\StateMachineValidator;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\WorkflowInterface;
use Throwable;

use function in_array;
use function React\Promise\all;
use function React\Promise\reject;
use function React\Promise\resolve;

final class DaemonController
{
    public const RETRY_LIMIT = 60;

    private const AUTO_RESTART_STATUSES = [
        DaemonUnit::STATUS_FINISHED,
        DaemonUnit::STATUS_FAILED,
    ];

    /**
     * @var \Symfony\Component\Workflow\WorkflowInterface
     */
    private WorkflowInterface $workflow;

    /**
     * @var \BetaKiller\Daemon\Supervisor\DaemonUnitCollectionInterface
     */
    private DaemonUnitCollectionInterface $registry;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    private LoopInterface $loop;

    private bool $isStoppingDaemons = false;

    private bool $isRestartingDaemons = false;

    /**
     * @var TimerInterface[]
     */
    private array $systemTimers = [];

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private ConfigProviderInterface $config;

    /**
     * DaemonController constructor.
     *
     * @param \BetaKiller\Console\ConsoleTaskLocatorInterface $taskLocator
     * @param \BetaKiller\Env\AppEnvInterface                 $appEnv
     * @param \BetaKiller\Config\ConfigProviderInterface      $config
     * @param \React\EventLoop\LoopInterface                  $loop
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(
        private ConsoleTaskLocatorInterface $taskLocator,
        AppEnvInterface $appEnv,
        ConfigProviderInterface $config,
        LoopInterface $loop,
        LoggerInterface $logger
    ) {
        $this->appEnv = $appEnv;
        $this->config = $config;
        $this->loop   = $loop;
        $this->logger = $logger;

        $this->registry = new DaemonUnitCollection();
        $this->workflow = $this->makeWorkflow();
    }

    private function makeWorkflow(): WorkflowInterface
    {
        $builder = new DefinitionBuilder();

        $builder->addPlaces([
            DaemonUnit::STATUS_LOADING,
            DaemonUnit::STATUS_STARTING,
            DaemonUnit::STATUS_RUNNING,
            DaemonUnit::STATUS_STOPPING,
            DaemonUnit::STATUS_STOPPED,
            DaemonUnit::STATUS_FAILED,
            DaemonUnit::STATUS_FINISHED,
        ]);

        $builder->addTransitions([
            // Start commands
            new Transition(DaemonUnit::COMMAND_START, DaemonUnit::STATUS_LOADING, DaemonUnit::STATUS_STARTING),
            new Transition(DaemonUnit::COMMAND_STOP, DaemonUnit::STATUS_RUNNING, DaemonUnit::STATUS_STOPPING),

            // Restart commands
            new Transition(DaemonUnit::COMMAND_START, DaemonUnit::STATUS_STOPPED, DaemonUnit::STATUS_STARTING),
            new Transition(DaemonUnit::COMMAND_START, DaemonUnit::STATUS_FINISHED, DaemonUnit::STATUS_STARTING),

            // Lock commands
            new Transition(DaemonUnit::COMMAND_DISABLE, DaemonUnit::STATUS_FAILED, DaemonUnit::STATUS_STOPPED),

            // Normal processing
            new Transition(DaemonUnit::EVENT_STARTED, DaemonUnit::STATUS_STARTING, DaemonUnit::STATUS_RUNNING),
            new Transition(DaemonUnit::EVENT_FINISHED, DaemonUnit::STATUS_RUNNING, DaemonUnit::STATUS_FINISHED),
            new Transition(DaemonUnit::EVENT_FINISHED, DaemonUnit::STATUS_STOPPING, DaemonUnit::STATUS_STOPPED),

            // Errors during processing
            new Transition(DaemonUnit::EVENT_FAILED, DaemonUnit::STATUS_STARTING, DaemonUnit::STATUS_FAILED),
            new Transition(DaemonUnit::EVENT_FAILED, DaemonUnit::STATUS_RUNNING, DaemonUnit::STATUS_FAILED),
            new Transition(DaemonUnit::EVENT_FAILED, DaemonUnit::STATUS_STOPPING, DaemonUnit::STATUS_FAILED),
        ]);

        $validator = new StateMachineValidator();

        $definition = $builder->build();

        // Check workflow is OK
        $validator->validate($definition, 'Daemon workflow');

        $marking = new MethodMarkingStore(true, 'status');

        return new Workflow($definition, $marking);
    }

    public function bindSystemCounters(): void
    {
        // Reset failure counters each 60 seconds
        $this->systemTimers[] = $this->loop->addPeriodicTimer(60, function () {
            foreach ($this->registry->getRunning() as $unit) {
                $unit->resetFailureCounter();
            }
        });
    }

    public function removeSystemCounters(): void
    {
        foreach ($this->systemTimers as $systemTimer) {
            $this->loop->cancelTimer($systemTimer);
        }
    }

    public function startAll(): PromiseInterface
    {
        $startPromises = [];

        foreach ($this->getDefinedDaemons() as $codename) {
            $startPromises[] = $this->start($codename);
        }

        // Async start
        $allPromise = all($startPromises);

        $allPromise->done(function () {
            $this->logger->info('All daemons are started');
        });

        $allPromise->otherwise(function () {
            $this->logger->warning('Daemons start failed');
        });

        return $allPromise;
    }

    public function start(string $name): PromiseInterface
    {
        if ($this->registry->has($name)) {
            throw new DaemonException('Can not start Daemon ":name" - unit already exists', [
                ':name' => $name,
            ]);
        }

        $unit = new DaemonUnit($name);

        $promise = $this->startDaemon($unit);

        $promise->then(
            function () use ($unit, $name) {
                $this->logger->debug('Started daemon ":name"', [
                    ':name' => $name,
                ]);

                $this->registry->add($unit);
            },
            function () use ($name) {
                $this->logger->error('Daemon ":name" start failed', [
                    ':name' => $name,
                ]);
            },
        );

        return $promise;
    }

    public function stop(string $name): PromiseInterface
    {
        if (!$this->registry->has($name)) {
            throw new DaemonException('Can not stop missing Daemon ":name"', [
                ':name' => $name,
            ]);
        }

        $unit = $this->registry->get($name);

        return $this->stopDaemon($unit);
    }

    public function stopAll(): PromiseInterface
    {
        if ($this->isStoppingDaemons || $this->isRestartingDaemons) {
            return reject();
        }

        $this->isStoppingDaemons = true;

        $stopPromises = [];

        $this->logger->debug('Stopping all daemons');

        foreach ($this->registry->getRunning() as $unit) {
            $stopPromises[] = $this->stopDaemon($unit);
        }

        $deferred = new Deferred();

        // Async stop
        $allPromise = all($stopPromises);

        $allPromise->done(function () {
            $this->logger->info('All daemons are stopped');
        });

        $allPromise->otherwise(function () {
            $this->logger->warning('Daemons stop failed');
        });

        $allPromise->always(function () use ($deferred) {
            $this->isStoppingDaemons = false;
            $deferred->resolve();
        });

        return $deferred->promise();
    }

    public function restart(string $name): PromiseInterface
    {
        if (!$this->registry->has($name)) {
            throw new DaemonException('Can not restart missing Daemon ":name"', [
                ':name' => $name,
            ]);
        }

        $unit = $this->registry->get($name);

        // Sync restart
        return $this->restartDaemon($unit, false);
    }

    public function restartStopped(): PromiseInterface
    {
        if ($this->isStoppingDaemons || $this->isRestartingDaemons) {
            return reject();
        }

        $this->isRestartingDaemons = true;

        $restartPromises = [];

        $this->logger->debug('Reloading stopped daemons');

        foreach ($this->registry->getStopped() as $unit) {
            $restartPromises[] = $this->restartDaemon($unit, true);
        }

        // Sync restart
        $allPromise = all($restartPromises);

        $allPromise->done(function () use ($restartPromises) {
            $this->logger->info(':count stopped daemons are reloaded', [
                ':count' => count($restartPromises),
            ]);
        });

        $allPromise->otherwise(function () {
            $this->logger->warning('Daemons restart failed');
        });

        $allPromise->always(function () {
            $this->isRestartingDaemons = false;
        });

        return $allPromise;
    }

    public function restartRunning(): PromiseInterface
    {
        if ($this->isStoppingDaemons || $this->isRestartingDaemons) {
            return reject();
        }

        $this->isRestartingDaemons = true;

        $this->logger->debug('Restarting running daemons');

        $restartPromises = [];

        foreach ($this->registry->getRunning() as $unit) {
            $restartPromises[] = $this->restartDaemon($unit, true);
        }

        // Sync restart
        $allPromise = all($restartPromises);

        $allPromise->done(function () use ($restartPromises) {
            $this->logger->info(':count running daemons are restarted', [
                ':count' => count($restartPromises),
            ]);
        });

        $allPromise->otherwise(function () {
            $this->logger->warning('Daemons restart failed');
        });

        $allPromise->always(function () {
            $this->isRestartingDaemons = false;
        });

        return $allPromise;
    }

    private function isRetryLimitReached(DaemonUnitInterface $unit): bool
    {
        // Retry until it will be fixed in dev mode
        if ($this->appEnv->inDevelopmentMode()) {
            return false;
        }

        return $unit->getFailureCounter() > self::RETRY_LIMIT;
    }

    private function startDaemon(DaemonUnitInterface $unit, bool $clearCounter = null): PromiseInterface
    {
        $this->checkIsCommandAllowed($unit, DaemonUnit::COMMAND_START);

        $this->applyUnitCommand($unit, DaemonUnit::COMMAND_START);

        if ($clearCounter) {
            // Clear failure counter
            $unit->resetFailureCounter();
        }

        return $this->startProcess($unit);
    }

    private function stopDaemon(DaemonUnitInterface $unit): PromiseInterface
    {
        $this->checkIsCommandAllowed($unit, DaemonUnit::COMMAND_STOP);

        $this->applyUnitCommand($unit, DaemonUnit::COMMAND_STOP);

        return $this->stopProcess($unit);
    }

    private function restartDaemon(DaemonUnitInterface $unit, bool $clearCounter): PromiseInterface
    {
        $this->logger->info('Daemon ":name" is restarting', [
            ':name' => $unit->getName(),
        ]);

        // Stop if daemon is running
        $stopPromise = $this->isCommandAllowed($unit, DaemonUnit::COMMAND_STOP)
            ? $this->stopDaemon($unit)
            : resolve();

        return $stopPromise->then(function () use ($unit, $clearCounter) {
            // Start right after the stop is completed
            return $this->startDaemon($unit, $clearCounter);
        });

//        try {
//            // Sync wait for Daemon stop (restart daemons one-by-one)
//            await($stopPromise, Factory::create(), AbstractDaemon::SHUTDOWN_TIMEOUT);
//        } catch (TimeoutException $e) {
//            return reject($e);
//        }
    }

    private function checkIsCommandAllowed(DaemonUnitInterface $unit, string $command): void
    {
        if (!$this->isCommandAllowed($unit, $command)) {
            throw new DaemonException('Daemon ":name" can not handle Command ":cmd" in ":status" state', [
                ':name'   => $unit->getName(),
                ':cmd'    => $command,
                ':status' => $unit->getStatus(),
            ]);
        }
    }

    private function isCommandAllowed(DaemonUnitInterface $unit, string $command): bool
    {
        return $this->workflow->can($unit, $command);
    }

    private function applyUnitCommand(DaemonUnitInterface $unit, string $command): void
    {
        $this->workflow->apply($unit, $command);

        $this->logger->debug('Daemon ":name" command ":cmd" applied, result status is ":status"', [
            ':cmd'    => $command,
            ':name'   => $unit->getName(),
            ':status' => $unit->getStatus(),
        ]);
    }

    private function emitUnitEvent(DaemonUnitInterface $unit, string $event): void
    {
        if (!$this->workflow->can($unit, $event)) {
            throw new DaemonException('Daemon ":name" in state ":status" can not emit event ":event"', [
                ':name'   => $unit->getName(),
                ':status' => $unit->getStatus(),
                ':event'  => $event,
            ]);
        }

        $this->workflow->apply($unit, $event);

        $this->logger->debug('Daemon ":name" event ":event" emitted, result status is ":status"', [
            ':event'  => $event,
            ':name'   => $unit->getName(),
            ':status' => $unit->getStatus(),
        ]);
    }

    private function startProcess(DaemonUnitInterface $unit): PromiseInterface
    {
        if ($unit->hasProcess()) {
            throw new DaemonException('Daemon ":name" in state ":status" has a Process already', [
                ':name'   => $unit->getName(),
                ':status' => $unit->getStatus(),
            ]);
        }

        $deferred = new Deferred;
        $promise  = $deferred->promise();

        $name = $unit->getName();

        $this->logger->debug('Starting ":name" daemon', [
            ':name' => $name,
        ]);

        $cmd = $this->taskLocator->getTaskCmd('daemon:runner', [
            'name' => $name,
        ]);

        $docRoot = $this->appEnv->getDocRootPath();

        $process = new Process($cmd, $docRoot);

//        $lock = $this->lockFactory->create($name);

        // Listen to process stop
        $process->on('exit', function ($exitCode, $termSignal) use ($unit, $name) {
            $this->logger->debug('Daemon ":name" exited with :code code and :signal signal', [
                ':name'   => $name,
                ':code'   => $exitCode ?? 'unknown',
                ':signal' => $termSignal ?? 'unknown',
            ]);

            // Remove Process binding
            $unit->clearProcess();

            // Can be null in some cases
            $exitCode   = $exitCode ?? DaemonInterface::EXIT_CODE_OK;
            $isSignaled = $termSignal !== null;

            $isOk = $isSignaled || $exitCode === DaemonInterface::EXIT_CODE_OK;

//            $this->checkLockReleased($lock);

            $targetEvent = $isOk ? DaemonUnit::EVENT_FINISHED : DaemonUnit::EVENT_FAILED;

            $this->emitUnitEvent($unit, $targetEvent);

            // Handle Process
            if ($isOk) {
                $this->handleFinishedProcess($unit);
            } else {
                $this->handleFailedProcess($unit);
            }
        });

        // On successful startup
        $promise->done(function () use ($unit, $name) {
            $this->emitUnitEvent($unit, DaemonUnit::EVENT_STARTED);
        });

        // On startup error
        $promise->otherwise(function () use ($unit, $name) {
            $this->emitUnitEvent($unit, DaemonUnit::EVENT_FAILED);
        });

        // Ensure task is running
        $pollingTimer = $this->loop->addPeriodicTimer(0.5, function () use ($name, $deferred, $process) {
            $this->logger->debug('Daemon ":name" startup check', [
                ':name' => $name,
            ]);

            if ($process->isRunning()) {
                $deferred->resolve();
            }
        });

        // Anyway
        $promise->always(function () use ($pollingTimer) {
            $this->loop->cancelTimer($pollingTimer);
        });

        $unit->bindToProcess($process);

        try {
            $process->start($this->loop);
        } catch (Throwable) {
            $deferred->reject();
        }

        return $promise;
    }

    private function stopProcess(DaemonUnitInterface $unit): PromiseInterface
    {
        if (!$unit->hasProcess()) {
            throw new DaemonException('Daemon ":name" in state ":status" has no Process', [
                ':name'   => $unit->getName(),
                ':status' => $unit->getStatus(),
            ]);
        }

        $name = $unit->getName();

        $this->logger->debug('Stopping ":name" daemon', [
            ':name' => $name,
        ]);

//        $status = $this->getStatus($name);
//
//        $ignoreStatuses = [
//            self::STATUS_FINISHED,
//            self::STATUS_STOPPING,
//            self::STATUS_STOPPED,
//            self::STATUS_FAILED,
//        ];
//
//        // Skip processes which are restarting already (race condition with event handler)
//        if (in_array($status, $ignoreStatuses, true)) {
//            $this->logger->debug('Daemon ":name" is stopped already, skipping', [
//                ':name' => $name,
//            ]);
//
//            // Already stopping/stopped => nothing to do here
//            return resolve();
//        }

        $deferred = new Deferred();
        $promise  = $deferred->promise();

        $process = $unit->getProcess();

//        $lock = $this->lockFactory->create($name);
//
//        if ($process->isRunning() && !$lock->isAcquired()) {
//            $this->logger->warning('Daemon ":name" is running but has no acquired lock', [
//                ':name' => $name,
//            ]);
//        }

        $stopTimeout = AbstractDaemon::SHUTDOWN_TIMEOUT + 1;

        $pollingTimer = $this->loop->addPeriodicTimer(0.5, function (TimerInterface $timer) use ($unit, $deferred) {
            // Wait for an actual stop (set by "exit" Process event handler)
            if (!$unit->inStatus(DaemonUnit::STATUS_STOPPED) && !$unit->inStatus(DaemonUnit::STATUS_FAILED)) {
                return;
            }

            // Stop polling
            $this->loop->cancelTimer($timer);

            // Notify about successful daemon stop
            $deferred->resolve();
        });

        // Stop timeout timer
        $timeoutTimer = $this->loop->addTimer(
            $stopTimeout,
            function () use ($pollingTimer, $deferred, $name, $process, $stopTimeout) {
                // Stop polling
                $this->loop->cancelTimer($pollingTimer);

                if (!$process->isRunning()) {
                    // Notify about successful daemon stop
                    $deferred->resolve();
                } else {
                    $this->logger->warning('Daemon ":name" had not been stopped in :timeout seconds', [
                        ':name'    => $name,
                        ':timeout' => $stopTimeout,
                    ]);

                    $deferred->reject();
                }
            }
        );

        $promise->always(function () use ($timeoutTimer, $pollingTimer) {
            $this->loop->cancelTimer($pollingTimer);
            $this->loop->cancelTimer($timeoutTimer);
        });

        $promise->done(static function () use ($unit) {
            // Clear counter for a fresh next start
            $unit->resetFailureCounter();
        });

        $promise->otherwise(function () use ($unit, $process) {
            if (!$process->isRunning()) {
                $this->emitUnitEvent($unit, DaemonUnit::EVENT_FINISHED);
            }
        });

        /**
         * Actual stop processing is placed below
         */

        $this->logger->debug('Sending "stop" signal to ":name" daemon with PID = :pid', [
            ':pid'  => $process->getPid(),
            ':name' => $name,
        ]);

        // Send stop signal to the daemon
        if (!$process->terminate(Runner::SIGNAL_SHUTDOWN)) {
            $this->logger->warning('Daemon ":name" had not been stopped (signaling failed)', [
                ':name' => $name,
            ]);

            // No signaling => failed
            $deferred->reject();
        }

        return $promise;
    }

    private function handleFinishedProcess(DaemonUnitInterface $unit): void
    {
        $this->logger->debug('Daemon ":name" had finished', [
            ':name' => $unit->getName(),
        ]);

        // Restart finished task
        if ($this->isAutoRestartAllowed($unit)) {
            $this->restartDaemon($unit, false);
        }
    }

    private function handleFailedProcess(DaemonUnitInterface $unit): void
    {
        $unit->incrementFailureCounter();

        if ($this->isRetryLimitReached($unit)) {
            // Do not try to restart this failing task
            $this->applyUnitCommand($unit, DaemonUnit::COMMAND_DISABLE);

            // Warning for developers
            $this->logger->emergency('Daemon ":name" had failed :times times and was disabled', [
                ':name'  => $unit->getName(),
                ':times' => $unit->getFailureCounter(),
            ]);
        }

        if ($this->isAutoRestartAllowed($unit)) {
            // Warning for developers
            $this->logger->warning('Daemon ":name" had failed and will be restarted immediately', [
                ':name' => $unit->getName(),
            ]);

            if (!$this->appEnv->inProductionMode()) {
                // Prevent high CPU usage on local errors
                sleep(2);
            }

            // Restart failed task
            $this->restartDaemon($unit, false);
        } else {
            // Warning for developers
            $this->logger->warning('Daemon ":name" had failed and will be stopped', [
                ':name' => $unit->getName(),
            ]);
        }
    }

    private function getDefinedDaemons(): array
    {
        return \array_unique((array)$this->config->load('daemons', []));
    }

    private function isAutoRestartAllowed(DaemonUnitInterface $unit): bool
    {
        return !$this->isStoppingDaemons && in_array($unit->getStatus(), self::AUTO_RESTART_STATUSES, true);
    }
}
