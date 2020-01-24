<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Cron\ConfigItem;
use BetaKiller\Cron\CronException;
use BetaKiller\Cron\Task;
use BetaKiller\Cron\TaskQueue;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Model\CronLog;
use BetaKiller\Model\CronLogInterface;
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
    private $env;

    /**
     * @var \BetaKiller\Cron\TaskQueue
     */
    private $queue;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $currentStage;

    /**
     * @var bool
     */
    private $isHuman;

    /**
     * @var \BetaKiller\Repository\CronLogRepositoryInterface
     */
    private $repo;

    /**
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private $maintenanceMode;

    /**
     * Cron constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface                $env
     * @param \BetaKiller\Cron\TaskQueue                        $queue
     * @param \BetaKiller\Repository\CronLogRepositoryInterface $repo
     * @param \BetaKiller\Service\MaintenanceModeService        $maintenanceMode
     * @param \Psr\Log\LoggerInterface                          $logger
     */
    public function __construct(
        AppEnvInterface $env,
        TaskQueue $queue,
        CronLogRepositoryInterface $repo,
        MaintenanceModeService $maintenanceMode,
        LoggerInterface $logger
    ) {
        $this->env    = $env;
        $this->queue  = $queue;
        $this->logger = $logger;

        parent::__construct();
        $this->repo            = $repo;
        $this->maintenanceMode = $maintenanceMode;
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
            $cron   = $item->getExpression();

            $previousRun = \DateTimeImmutable::createFromMutable($cron->getPreviousRunDate());

            $this->logDebug('Checking task [:task] is missed after :date', [
                ':task' => $name,
                ':date' => $previousRun->format(\DateTimeImmutable::ATOM),
            ]);

            if (!$this->repo->hasTaskRecordAfter($name, $params, $previousRun)) {
                $this->logDebug('Task [:task] is missing previous run', [':task' => $name]);

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
        $task = new Task($name, $params);

        // Skip task if already queued
        if ($this->queue->isQueued($task)) {
            $this->logger->info('Task [:task] is already queued, skipping', [':task' => $name]);

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

        $tasks = $this->queue->getReadyToStart();

        if ($tasks && $this->maintenanceMode->isEnabled()) {
            $this->logger->info('CRON jobs (:count) enqueued but not processed coz of maintenance mode', [
                ':count' => count($tasks),
            ]);

            return;
        }

        // Select queued tasks where start_at >= current time, limit 5
        // It allows to postpone failed tasks for 5 minutes
        foreach ($tasks as $task) {
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

    private function makeTaskRun(Task $task): RunInterface
    {
        $this->logDebug('Task [:name] is ready to start', [':name' => $task->getName()]);

        $name   = $task->getName();
        $params = $task->getParams();

        $cmd     = self::getTaskCmd($this->env, $name, $params);
        $docRoot = $this->env->getDocRootPath();

        $log = new CronLog();

        $log
            ->setName($name)
            ->setParams($params)
            ->setCmd($cmd)
            ->markAsQueued();

        $this->repo->save($log);

        // Store fingerprint for simpler task identification upon start
        $tags = [
            'name'                => $task->getName(),
            self::TAG_LOG_ID      => $log->getID(),
            self::TAG_FINGERPRINT => $task->getFingerprint(),
        ];

        $this->logDebug('Command: :cmd', [':cmd' => $cmd]);

        $run = new ProcessRun(Process::fromShellCommandline($cmd, $docRoot), $tags);

        $run->addListener(RunEvent::STARTED, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            $task->started();

            $this->logDebug('Task [:name] is started', [':name' => $task->getName()]);

            $log = $this->getLogFromRunEvent($event);
            $log->markAsStarted();
            $this->repo->save($log);
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
            $this->repo->save($log);
        });

        $run->addListener(RunEvent::FAILED, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            $task->failed();

            $till = (new \DateTimeImmutable)->add(new \DateInterval('PT15M'));
            $task->postpone($till);

            $this->logger->warning('Task [:name] is failed and postponed till :time', [
                ':name' => $task->getName(),
                ':time' => $till->format('H:i:s d.m.Y'),
            ]);

            // TODO Real enqueue and postpone (use ESB command queue)
            $log = $this->getLogFromRunEvent($event);
            $log->markAsFailed();
            $this->repo->save($log);
        });

        return $run;
    }

    /**
     * @param \Graze\ParallelProcess\Event\RunEvent $event
     *
     * @return \BetaKiller\Cron\Task
     * @throws \BetaKiller\Cron\CronException
     */
    private function getTaskFromRunEvent(RunEvent $event): Task
    {
        $tags        = $event->getRun()->getTags();
        $fingerprint = $tags[self::TAG_FINGERPRINT] ?? '';

        if (!$fingerprint) {
            throw new CronException('Missing process fingerprint, tags are :values', [
                ':values' => \json_encode($tags),
            ]);
        }

        return $this->queue->getByFingerprint($fingerprint);
    }

    private function getLogFromRunEvent(RunEvent $event): CronLogInterface
    {
        $tags  = $event->getRun()->getTags();
        $logID = $tags[self::TAG_LOG_ID] ?? '';

        if (!$logID) {
            throw new CronException('Missing process log ID, tags are :values', [
                ':values' => \json_encode($tags),
            ]);
        }

        return $this->repo->getById($logID);
    }

    private function logDebug(string $message, array $params = null): void
    {
        if (!$this->isHuman) {
            $this->logger->debug($message, $params);
        }
    }
}
