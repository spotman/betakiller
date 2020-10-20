<?php
namespace BetaKiller\Cron;


use DateTimeImmutable;

class TaskQueue
{
    /**
     * @var \BetaKiller\Cron\CronTask[]
     */
    private $queue = [];

    /**
     * TODO Link queue to concrete stage so each stage will have its own independent queue
     *
     * @var string
     */
    private $stage;

    /**
     * @param \BetaKiller\Cron\CronTask $task
     */
    public function enqueue(CronTask $task): void
    {
        $this->queue[$task->getFingerprint()] = $task;

        // Mark task as enqueued
        $task->enqueued();
    }

    /**
     * @param \BetaKiller\Cron\CronTask $task
     *
     * @throws \BetaKiller\Cron\CronException
     */
    public function dequeue(CronTask $task): void
    {
        if (!$this->isQueued($task)) {
            throw new CronException('Task is not enqueued? can not dequeue');
        }

        unset($this->queue[$task->getFingerprint()]);
    }

    public function isQueued(CronTask $task): bool
    {
        return isset($this->queue[$task->getFingerprint()]);
    }

    /**
     * @param int $pid
     *
     * @return \BetaKiller\Cron\CronTask
     * @throws \BetaKiller\Cron\CronException
     */
    public function getByPID(int $pid): CronTask
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
     * @return \BetaKiller\Cron\CronTask
     * @throws \BetaKiller\Cron\CronException
     */
    public function getByFingerprint(string $fingerprint): CronTask
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
     * @return CronTask[]
     */
    public function getReadyToStart(?DateTimeImmutable $startTime = null): array
    {
        $startTime = $startTime ?? new DateTimeImmutable;

        return array_filter($this->queue, static function (CronTask $task) use ($startTime) {
            return $task->getStartAt() <= $startTime;
        });
    }
}
