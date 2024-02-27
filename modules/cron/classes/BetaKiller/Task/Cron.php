<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Cron\ConfigItem;
use BetaKiller\Cron\CronLockFactory;
use BetaKiller\Cron\CronTask;
use BetaKiller\Cron\TaskQueue;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\DateTimeHelper;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\CronCommand;
use BetaKiller\Model\CronLog;
use BetaKiller\Model\CronLogInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Repository\CronCommandRepositoryInterface;
use BetaKiller\Repository\CronLogRepositoryInterface;
use BetaKiller\Service\MaintenanceModeService;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class Cron extends AbstractTask
{
    private const TAG_FINGERPRINT = 'fingerprint';
    private const TAG_LOG_ID      = 'log-id';

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $env;

    /**
     * @var \BetaKiller\Cron\TaskQueue
     */
    private TaskQueue $queue;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string
     */
    private string $currentStage;

    /**
     * @var \BetaKiller\Repository\CronLogRepositoryInterface
     */
    private CronLogRepositoryInterface $logRepo;

    /**
     * @var \BetaKiller\Repository\CronCommandRepositoryInterface
     */
    private CronCommandRepositoryInterface $cmdRepo;

    /**
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private MaintenanceModeService $maintenanceMode;

    /**
     * @var \BetaKiller\Cron\CronLockFactory
     */
    private CronLockFactory $lockFactory;

    /**
     * @var \BetaKiller\ProcessLock\LockInterface[]
     */
    private array $locks = [];

    /**
     * Cron constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface                       $env
     * @param \BetaKiller\Cron\TaskQueue                            $queue
     * @param \BetaKiller\Repository\CronLogRepositoryInterface     $logRepo
     * @param \BetaKiller\Repository\CronCommandRepositoryInterface $cmdRepo
     * @param \BetaKiller\Service\MaintenanceModeService            $maintenanceMode
     * @param \BetaKiller\Cron\CronLockFactory                      $lockFactory
     * @param \Psr\Log\LoggerInterface                              $logger
     */
    public function __construct(
        AppEnvInterface                $env,
        TaskQueue                      $queue,
        CronLogRepositoryInterface     $logRepo,
        CronCommandRepositoryInterface $cmdRepo,
        MaintenanceModeService         $maintenanceMode,
        CronLockFactory                $lockFactory,
        LoggerInterface                $logger
    ) {
        $this->env    = $env;
        $this->queue  = $queue;
        $this->logger = $logger;

        $this->logRepo         = $logRepo;
        $this->cmdRepo         = $cmdRepo;
        $this->maintenanceMode = $maintenanceMode;
        $this->lockFactory     = $lockFactory;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [
            // No options
        ];
    }

    /**
     * @throws \BetaKiller\Task\TaskException
     */
    public function run(): void
    {
        if ($this->maintenanceMode->isEnabled()) {
            $this->logger->debug('CRON jobs would not be processed coz of maintenance mode');

            return;
        }

        $this->currentStage = $this->env->getModeName();

        // Enqueue all tasks which was missed in past (power outage, script error, etc)
        $this->enqueueMissedTasks();

        // Get all due tasks and enqueue them (long-running task would not affect next tasks due check)
        $this->enqueueDueTasks();

        // Get all queued tasks and run them one by one
        $this->runQueuedTasks();

        $this->logger->info('CRON tasks processed');
    }

    /**
     * @return \Generator|ConfigItem[]
     * @throws \BetaKiller\Task\TaskException
     */
    private function getConfigTasksGenerator(): \Generator
    {
        $sitePath = $this->env->getAppRootPath();

        $cronFile = $sitePath.DIRECTORY_SEPARATOR.'crontab.yml';

        /** @var array[] $records */
        $records = Yaml::parseFile($cronFile, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        if (!$records) {
            throw new TaskException('Missing crontab.yml');
        }

        foreach ($records as $data) {
            $item = ConfigItem::fromArray($data);

            $taskStages = $item->getStages();

            if (!$taskStages) {
                // No stage means any stage
                $taskStages[] = $this->currentStage;
            }

            // Ensure that target stage is reached
            if (!\in_array($this->currentStage, $taskStages, true)) {
                $this->logDebug('Skip task [:name] for stages ":stage"', [
                    ':name'  => $item->getName(),
                    ':stage' => implode('", "', $taskStages),
                ]);

                continue;
            }

            yield $item;
        }
    }

    private function enqueueMissedTasks(): void
    {
        foreach ($this->getConfigTasksGenerator() as $item) {
            $name   = $item->getName();
            $params = $item->getParams();

            $previousRun = $item->getPreviousRunDate();

            $tz = DateTimeHelper::getUtcTimezone();

            $this->logDebug('Checking task [:task] is missed after :date', [
                ':task' => $name,
                ':date' => $previousRun->setTimezone($tz)->format(DateTimeImmutable::ATOM),
            ]);

            $cmd = $this->cmdRepo->findByNameAndParams($name, $params);

            if (!$cmd || !$this->logRepo->hasTaskRecordAfter($cmd, $previousRun)) {
                $this->logDebug('Task [:task] is missing previous run', [
                    ':task' => $name,
                ]);

                $this->enqueueTask($name, $params);
            }
        }
    }

    /**
     * @throws \BetaKiller\Task\TaskException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function enqueueDueTasks(): void
    {
        foreach ($this->getConfigTasksGenerator() as $item) {
            $name = $item->getName();
            $cron = $item->getExpression();

            if ($cron->isDue()) {
                $this->logDebug('Task [:task] is due', [':task' => $name]);

                $this->enqueueTask($name, $item->getParams());
            }
        }
    }

    private function enqueueTask(string $name, array $params): void
    {
        $task = new CronTask($name, $params);

        // Skip task if already queued
        if ($this->queue->isQueued($task)) {
            $this->logDebug('Task [:task] is already queued, skipping', [':task' => $name]);

            return;
        }

        $lock = $this->getLockFor($task);

        if ($lock->isValid()) {
            $this->logger->warning('Cron task ":name" is still running, skip it', [
                ':name' => $task->getName(),
            ]);

            // Skip this task
            return;
        }

        // Enqueue task
        $this->queue->enqueue($task);

        $this->logDebug('Task [:task] was queued', [':task' => $name]);
    }

    /**
     * @throws \Exception
     */
    private function runQueuedTasks(): void
    {
        // Select queued tasks where start_at >= current time, limit 5
        // It allows to postpone failed tasks for 5 minutes
        foreach ($this->queue->getReadyToStart() as $task) {
            $this->startTaskProcess($task);
        }
    }

    private function startTaskProcess(CronTask $task): void
    {
        $this->logDebug('Task [:name] is ready to start', [':name' => $task->getName()]);

        $name   = $task->getName();
        $params = $task->getParams();

        $cmd = self::getTaskCmd($this->env, $name, $params, true);

        $command = $this->cmdRepo->findByNameAndParams($name, $params);

        if (!$command) {
            $command = new CronCommand;

            $command
                ->setName($name)
                ->setParams($params)
                ->setCmd($cmd);

            $this->cmdRepo->save($command);
        }

        $log = new CronLog();

        $log
            ->setCommand($command)
            ->markAsQueued();

        $this->logRepo->save($log);

        $this->logDebug('Command: :cmd', [
            ':cmd' => $cmd,
        ]);

        $docRoot = $this->env->getDocRootPath();

        $process = new Process($cmd, $docRoot);

        $process->on('exit', function ($exitCode, $termSignal) use ($task, $log) {
            $this->logger->debug('Task ":name" exited with :code code and :signal signal', [
                ':name'   => $task->getName(),
                ':code'   => $exitCode ?? 'unknown',
                ':signal' => $termSignal ?? 'unknown',
            ]);

            // Can be null in some cases
            $exitCode   = $exitCode ?? 0;
            $isSignaled = $termSignal !== null;

            $isOk = $isSignaled || $exitCode === 0;

            if ($isOk) {
                $this->onProcessDone($task, $log);
            } else {
                $this->onProcessFailed($task, $log);
            }
        });

        try {
            $process->start();
            $this->onProcessStarted($process, $task, $log);
        } catch (RuntimeException) {
            $this->onProcessFailed($task, $log);
        }
    }

    private function onProcessStarted(Process $process, CronTask $task, CronLogInterface $log): void
    {
        $pid = $process->getPid();

        // Store PID in CronTask record
        $task->started($pid);

        // Lock ASAP
        $this->acquireLock($task);

        // Forward output and errors (to collect them in the infrastructure)
        $process->stdout->on('data', function ($chunk) {
            fwrite(STDOUT, $chunk);
        });

        $process->stderr->on('data', function ($chunk) {
            fwrite(STDERR, $chunk);
        });

        $this->logDebug('Task [:name] is started with PID :pid', [
            ':name' => $task->getName(),
            ':pid'  => $task->getPID(),
        ]);

        $log->markAsStarted();
        $this->logRepo->save($log);
    }

    private function onProcessDone(CronTask $task, CronLogInterface $log): void
    {
        $task->done();
        $this->queue->dequeue($task);

        $this->logDebug('Task [:name] succeeded', [
            ':name' => $task->getName(),
        ]);

        $log->markAsSucceeded();
        $this->logRepo->save($log);

        // Keep locked until all processing is done
        $this->releaseLock($task);
    }

    private function onProcessFailed(CronTask $task, CronLogInterface $log): void
    {
        // TODO Real enqueue and postpone (use ESB command queue)
        $task->failed();

        $till = (new DateTimeImmutable)->add(new \DateInterval('PT15M'));
        $task->postpone($till);

        $this->logger->warning('Task [:name] is failed and postponed', [
            ':name' => $task->getName(),
        ]);

        $log->markAsFailed();
        $this->logRepo->save($log);

        // Keep locked until all processing is done
        $this->releaseLock($task);
    }

    private function acquireLock(CronTask $task): bool
    {
        $lock = $this->getLockFor($task);

        // Check if it is running already
        if ($lock->isValid()) {
            $this->logger->warning('Cron task ":name" is already running', [
                ':name' => $task->getName(),
            ]);

            // It is not normal to run cron tasks in parallel
            return false;
        }

        if ($lock->isAcquired()) {
            $this->logger->warning('Cron task ":name" lock is stale, releasing it', [
                ':name' => $task->getName(),
            ]);

            $lock->release();
        }

        if (!$lock->acquire($task->getPID())) {
            throw new TaskException('Can not acquire lock for cron task ":name"', [
                ':name' => $task->getName(),
            ]);
        }

        return $lock->isAcquired();
    }

    private function releaseLock(CronTask $task): void
    {
        // Task was not started => no lock acquired => nothing to do
        if (!$task->isStarted()) {
            return;
        }

        try {
            $lock = $this->getLockFor($task);

            if (!$lock->isAcquired()) {
                $this->logger->warning('Cron task ":name" is not locked, release skipped', [
                    ':name' => $task->getName(),
                ]);

                return;
            }

            if ($lock->release()) {
                $this->logger->debug('Cron task ":name" was unlocked', [
                    ':name' => $task->getName(),
                ]);
            }
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }

    private function getLockFor(CronTask $task): LockInterface
    {
        $codename = $task->getName();

        $params = [];

        // Add parameters to codename to separate locks for calls with different arguments
        foreach ($task->getParams() as $optionName => $optionValue) {
            $params[] = $optionName.'-'.$optionValue;
        }

        if ($params) {
            $codename .= '.'.\implode('.', $params);
        }

        if (!isset($this->locks[$codename])) {
            $this->locks[$codename] = $this->lockFactory->create($codename);
        }

        return $this->locks[$codename];
    }

    private function logDebug(string $message, array $params = null): void
    {
        $this->logger->debug($message, $params);
    }
}
