<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Cron\CronException;
use BetaKiller\Cron\Task;
use BetaKiller\Cron\TaskQueue;
use BetaKiller\Helper\AppEnvInterface;
use Cron\CronExpression;
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
    public const EXPR_HELPERS = [
        'hourly',
    ];

    private const FINGERPRINT_TAG = 'fingerprint';

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
     * Cron constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $env
     * @param \BetaKiller\Cron\TaskQueue         $queue
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(
        AppEnvInterface $env,
        TaskQueue $queue,
        LoggerInterface $logger
    ) {
        $this->env    = $env;
        $this->queue  = $queue;
        $this->logger = $logger;

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
        $this->isHuman      = $this->getOption('human') !== false;
        $this->currentStage = $this->env->getModeName();

        // Get all due tasks and enqueue them (long-running task would not affect next tasks due check)
        $this->enqueueDueTasks();

        // Get all queued tasks and run them one by one
        $this->runQueuedTasks();
    }

    /**
     * @throws \BetaKiller\Task\TaskException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function enqueueDueTasks(): void
    {
        $sitePath = $this->env->getAppRootPath();

        $cronFile = $sitePath.DIRECTORY_SEPARATOR.'crontab.yml';

        /** @var array[] $records */
        $records = Yaml::parseFile($cronFile, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        if (!$records) {
            throw new TaskException('Missing crontab.yml');
        }

        foreach ($records as $name => $data) {
            $expr       = $data['at'] ?? null;
            $params     = $data['params'] ?? null;
            $taskStages = $data['stages'] ?? null;

            if (!$expr) {
                throw new TaskException('Missing "at" key value in [:name] task', [':name' => $name]);
            }

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
                    ':name'  => $name,
                    ':stage' => implode('", "', $taskStages),
                ]);

                continue;
            }

            $cron = CronExpression::factory($expr);

            if (!$cron->isDue()) {
                $this->logDebug('Task [:task] is not due, skipping', [':task' => $name]);
                continue;
            }

            $this->logDebug('Task [:task] is due', [':task' => $name]);

            $task = new Task($name, $params);

            // Skip task if already queued
            if ($this->queue->isQueued($task)) {
                $this->logger->warning('Task [:task] is already queued, skipping', [':task' => $name]);
                continue;
            }

            // Enqueue task
            $this->queue->enqueue($task);

            $this->logDebug('Task [:task] was queued', [':task' => $name]);
        }
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

    private function makeTaskRun(Task $task): RunInterface
    {
        $this->logDebug('Task [:name] is ready to start', [':name' => $task->getName()]);

        $cmd = self::getTaskCmd($this->env, $task->getName(), $task->getParams());
        $docRoot = $this->env->getDocRootPath();

        // Store fingerprint for simpler task identification upon start
        $tags = [
            self::FINGERPRINT_TAG => $task->getFingerprint(),
            'name'                => $task->getName(),
        ];

        $run = new ProcessRun(Process::fromShellCommandline($cmd, $docRoot), $tags);

        $run->addListener(RunEvent::STARTED, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            $task->started();

            $this->logDebug('Task [:name] is started', [':name' => $task->getName()]);
        });

        $run->addListener(RunEvent::SUCCESSFUL, function (RunEvent $event) {
            $task = $this->getTaskFromRunEvent($event);

            $task->done();
            $this->queue->dequeue($task);

            $this->logDebug('Task [:name] succeeded', [
                ':name' => $task->getName(),
            ]);
        });

        $run->addListener(RunEvent::FAILED, function (RunEvent $runEvent) {
            $task = $this->getTaskFromRunEvent($runEvent);

            $task->failed();

            $till = (new \DateTimeImmutable)->add(new \DateInterval('PT15M'));
            $task->postpone($till);

            $this->logDebug('Task [:name] is failed and postponed till :time', [
                ':name' => $task->getName(),
                ':time' => $till->format('H:i:s d.m.Y'),
            ]);
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
        $fingerprint = $tags[self::FINGERPRINT_TAG] ?? '';

        if (!$fingerprint) {
            throw new CronException('Missing process fingerprint, tags are :values', [
                ':values' => \json_encode($tags),
            ]);
        }

        return $this->queue->getByFingerprint($fingerprint);
    }

    private function logDebug(string $message, array $params = null): void
    {
        if (!$this->isHuman) {
            $this->logger->debug($message, $params);
        }
    }
}
