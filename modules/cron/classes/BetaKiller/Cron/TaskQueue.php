<?php
namespace BetaKiller\Cron;


use DateTimeImmutable;

class TaskQueue
{
    /**
     * @var \BetaKiller\Cron\Task[]
     */
    private $queue = [];

    /**
     * TODO Link queue to concrete stage so each stage will have its own independent queue
     *
     * @var string
     */
    private $stage;

    /**
     * @param \BetaKiller\Cron\Task $task
     */
    public function enqueue(Task $task): void
    {
        $this->queue[$task->getFingerprint()] = $task;

        // Mark task as enqueued
        $task->enqueued();
    }

    /**
     * @param \BetaKiller\Cron\Task $task
     *
     * @throws \BetaKiller\Cron\CronException
     */
    public function dequeue(Task $task): void
    {
        if (!$this->isQueued($task)) {
            throw new CronException('Task is not enqueued? can not dequeue');
        }

        unset($this->queue[$task->getFingerprint()]);
    }

    public function isQueued(Task $task): bool
    {
        return isset($this->queue[$task->getFingerprint()]);
    }

    /**
     * @param int $pid
     *
     * @return \BetaKiller\Cron\Task
     * @throws \BetaKiller\Cron\CronException
     */
    public function getByPID(int $pid): Task
    {
        foreach ($this->queue as $task) {
            if ($task->getPID() === $pid) {
                return $task;
            }
        }

        throw new CronException('Missing process with pid = :value', [':value' => $pid]);
    }

    /**
     * @param string $fingerprint
     *
     * @return \BetaKiller\Cron\Task
     * @throws \BetaKiller\Cron\CronException
     */
    public function getByFingerprint(string $fingerprint): Task
    {
        foreach ($this->queue as $task) {
            if ($task->getFingerprint() === $fingerprint) {
                return $task;
            }
        }

        throw new CronException('Missing process with fingerprint = :value', [':value' => $fingerprint]);
    }

    /**
     * @param \DateTimeImmutable|null $startTime
     *
     * @return Task[]
     */
    public function getReadyToStart(?DateTimeImmutable $startTime = null): array
    {
        $startTime = $startTime ?? new DateTimeImmutable;

        return array_filter($this->queue, static function (Task $task) use ($startTime) {
            return $task->getStartAt() >= $startTime;
        });
    }
}
