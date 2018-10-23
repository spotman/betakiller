<?php
namespace BetaKiller\Cron;

use DateTimeImmutable;

class Task
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $fingerprint;

    /**
     * @var \DateTimeImmutable
     */
    private $queuedAt;

    /**
     * @var \DateTimeImmutable
     */
    private $startAt;

    /**
     * @var \DateTimeImmutable
     */
    private $finishedAt;

    /**
     * @var int|null
     */
    private $pid;

    /**
     * Task constructor.
     *
     * @param string                  $name
     * @param array|null              $params
     * @param \DateTimeImmutable|null $startAt
     */
    public function __construct(string $name, ?array $params = null, ?DateTimeImmutable $startAt = null)
    {
        $this->name    = $name;
        $this->params  = $params ?? [];
        $this->startAt = $startAt ?? new DateTimeImmutable;

        $this->calculateFingerprint();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getQueuedAt(): ?DateTimeImmutable
    {
        return $this->queuedAt;
    }

    public function getStartAt(): DateTimeImmutable
    {
        return $this->startAt;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    private function calculateFingerprint(): void
    {
        $this->fingerprint = sha1(\json_encode([$this->name, $this->params]));
    }

    public function enqueued(?DateTimeImmutable $queuedAt = null): void
    {
        $this->queuedAt = $queuedAt ?? new DateTimeImmutable;
    }

    public function started(/* int $pid, */ ?DateTimeImmutable $startTime = null): void
    {
//        $this->pid     = $pid;
        $this->startAt = $startTime ?? new DateTimeImmutable;
    }

    public function done(?DateTimeImmutable $stopTime = null): void
    {
        $this->clearPID();
        $this->finishedAt = $stopTime ?? new DateTimeImmutable;
    }

    public function failed(): void
    {
        $this->clearPID();
    }

    public function postpone(DateTimeImmutable $nextRunTime = null): void
    {
        $this->startAt = $nextRunTime;
    }

    public function getPID(): ?int
    {
        return $this->pid;
    }

    private function clearPID(): void
    {
        $this->pid = null;
    }
}
