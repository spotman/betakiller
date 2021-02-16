<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Cron\ConfigItem;
use BetaKiller\Cron\CronException;
use BetaKiller\Cron\CronLockFactory;
use BetaKiller\Cron\CronTask;
use BetaKiller\Cron\TaskQueue;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\DateTimeHelper;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\CronCommand;
use BetaKiller\Model\CronLog;
use BetaKiller\Model\CronLogInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Repository\CronCommandRepositoryInterface;
use BetaKiller\Repository\CronLogRepositoryInterface;
use BetaKiller\Service\MaintenanceModeService;
use Graze\ParallelProcess\Display\Table;
use Graze\ParallelProcess\Event\RunEvent;
use Graze\ParallelProcess\PriorityPool;
use Graze\ParallelProcess\ProcessRun;
use Graze\ParallelProcess\RunInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class Cron extends AbstractTask
{
    private const TAG_FINGERPRINT = 'fingerprint';
    private const TAG_LOG_ID      = 'log-id';

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
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
     * @var bool
     */
    private bool $isHuman;

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
     * @param \BetaKiller\Helper\AppEnvInterface                    $env
     * @param \BetaKiller\Cron\TaskQueue                            $queue
     * @param \BetaKiller\Repository\CronLogRepositoryInterface     $logRepo
     * @param \BetaKiller\Repository\CronCommandRepositoryInterface $cmdRepo
     * @param \BetaKiller\Service\MaintenanceModeService            $maintenanceMode
     * @param \BetaKiller\Cron\CronLockFactory                      $lockFactory
     * @param \Psr\Log\LoggerInterface                              $logger
     */
    public function __construct(
        AppEnvInterface $env,
        TaskQueue $queue,
        CronLogRepositoryInterface $logRepo,
        CronCommandRepositoryInterface $cmdRepo,
        MaintenanceModeService $maintenanceMode,
        CronLockFactory $lockFactory,
        LoggerInterface $logger
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
            'human' => false,
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

        $this->isHuman      = $this->getOption('human') !== false;
        $this->currentStage = $this->env->getModeName();

        // Enqueue all tasks which was missed in past (power outage, script error, etc)
        $this->enqueueMissedTasks();

        // Get all due tasks and enqueue them (long-running task would not affect next tasks due check)
        $this->enqueueDueTasks();

        // Get all queued tasks and run them one by one
        $this->runQueuedTasks();
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
                $taskStages = [$this->currentStage];
            }

            if (!\is_array($taskStages)) {
                throw new TaskException('Task stage must be an array');
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
                ':date' => $previousRun->setTimezone($tz)->format(\DateTimeImmutable::ATOM),
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
        $pool = new PriorityPool();
        $pool->setMaxSimultaneous(5);

        // Select queued tasks where start_at >= current time, limit 5
        // It allows to postpone failed tasks for 5 minutes
        foreach ($this->queue->getReadyToStart() as $task) {
            $pool->add($this->makeTaskRun($task));
        }

        if ($this->isHuman) {
            $verbosity = $this->env->isDebugEnabled()
                ? ConsoleOutput::VERBOSITY_DEBUG
                : ConsoleOutput::VERBOSITY_VERY_VERBOSE;

            $output = new ConsoleOutput($verbosity);
            $table  = new Table($output, $pool);

            $table->run();
        } else {
            $pool->run();
        }
    }

    private function makeTaskRun(CronTask $task): RunInterface
    {
        $this->logDebug('Task [:name] is ready to start', [':name' => $task->getName()]);

        $name   = $task->getName();
        $params = $task->getParams();

        $cmd = self::getTaskCmd($this->env, $name, $params);

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

        // Store fingerprint for simpler task identification upon start
        $tags = [
            'name'                => $task->getName(),
            self::TAG_LOG_ID      => $log->getID(),
            self::TAG_FINGERPRINT => $task->getFingerprint(),
        ];

        $this->logDebug('Command: :cmd', [
            ':cmd' => $cmd,
        ]);

        $docRoot = $this->env->getDocRootPath();

        $run = new ProcessRun(Process::fromShellCommandline($cmd, $docRoot), $tags);

        // Listen for UPDATED event coz STARTED is emitted before actual process is started
        $run->addListener(RunEvent::UPDATED, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            // Prevent duplicate processing on subsequent UPDATED events
            if ($task->isRunning()) {
                return;
            }

            $process = $this->getProcessFromRunEvent($event);

//            // Wait for the process to be really started
//            while (!$process->getPid()) {
//                \usleep(10000);
//            }

            // Prevent starting of a terminated task (race condition, will be processed by another handler below)
            if ($process->isTerminated()) {
                return;
            }

            // Store PID in CronTask record
            $task->started($process->getPid());

            // Lock ASAP
            $this->acquireLock($task);

            $this->logDebug('Task [:name] is started with PID :pid', [
                ':name' => $task->getName(),
                ':pid'  => $task->getPID(),
            ]);

            $log = $this->getLogFromRunEvent($event);
            $log->markAsStarted();
            $this->logRepo->save($log);
        });

        $run->addListener(RunEvent::SUCCESSFUL, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            $task->done();
            $this->queue->dequeue($task);

            $this->logDebug('Task [:name] succeeded', [
                ':name' => $task->getName(),
            ]);

            $log = $this->getLogFromRunEvent($event);
            $log->markAsSucceeded();
            $this->logRepo->save($log);

            // Keep locked until all processing is done
            $this->releaseLock($task);
        });

        $run->addListener(RunEvent::FAILED, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            $task->failed();

            $till = (new \DateTimeImmutable)->add(new \DateInterval('PT15M'));
            $task->postpone($till);

            $this->logger->warning('Task [:name] is failed and postponed', [
                ':name' => $task->getName(),
            ]);

            // TODO Real enqueue and postpone (use ESB command queue)
            $log = $this->getLogFromRunEvent($event);
            $log->markAsFailed();
            $this->logRepo->save($log);

            // Keep locked until all processing is done
            $this->releaseLock($task);
        });

        return $run;
    }

    /**
     * @param \Graze\ParallelProcess\Event\RunEvent $event
     *
     * @return \BetaKiller\Cron\CronTask
     * @throws \BetaKiller\Cron\CronException
     */
    private function getTaskFromRunEvent(RunEvent $event): CronTask
    {
        $tags        = $event->getRun()->getTags();
        $fingerprint = $tags[self::TAG_FINGERPRINT] ?? '';

        if (!$fingerprint) {
            throw new CronException('Missing process fingerprint, tags are :values', [
                ':values' => \json_encode($tags, JSON_THROW_ON_ERROR),
            ]);
        }

        return $this->queue->getByFingerprint($fingerprint);
    }

    private function getProcessFromRunEvent(RunEvent $event): Process
    {
        $run = $event->getRun();

        if (!$run instanceof ProcessRun) {
            throw new \LogicException('Event Run must implement ProcessRun');
        }

        return $run->getProcess();
    }

    private function getLogFromRunEvent(RunEvent $event): CronLogInterface
    {
        $tags  = $event->getRun()->getTags();
        $logID = $tags[self::TAG_LOG_ID] ?? '';

        if (!$logID) {
            throw new CronException('Missing process log ID, tags are :values', [
                ':values' => \json_encode($tags, JSON_THROW_ON_ERROR),
            ]);
        }

        return $this->logRepo->getById($logID);
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
        if (!$this->isHuman) {
            $this->logger->debug($message, $params);
        }
    }
}
