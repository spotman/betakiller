<?php
namespace BetaKiller\Cron;

use DateTimeImmutable;

class CronTask
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var array
     */
    private array $params;

    /**
     * @var string
     */
    private string $fingerprint;

    /**
     * @var \DateTimeImmutable
     */
    private DateTimeImmutable $startAt;

    /**
     * @var \DateTimeImmutable|null
     */
    private ?DateTimeImmutable $queuedAt = null;

    /**
     * @var \DateTimeImmutable|null
     */
    private ?DateTimeImmutable $finishedAt = null;

    /**
     * @var int|null
     */
    private ?int $pid = null;

    /**
     * CronTask constructor.
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

    public function getStartAt(): DateTimeImmutable
    {
        return $this->startAt;
    }

    public function getQueuedAt(): ?DateTimeImmutable
    {
        return $this->queuedAt;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    private function calculateFingerprint(): void
    {
        $this->fingerprint = sha1(\json_encode([$this->name, $this->params], JSON_THROW_ON_ERROR));
    }

    public function enqueued(?DateTimeImmutable $queuedAt = null): void
    {
        $this->queuedAt = $queuedAt ?? new DateTimeImmutable;
    }

    public function started(int $pid, ?DateTimeImmutable $startTime = null): void
    {
        $this->pid     = $pid;
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

    public function postpone(DateTimeImmutable $nextRunTime): void
    {
        $this->startAt = $nextRunTime;
    }

    public function getPID(): ?int
    {
        return $this->pid;
    }

    public function isRunning(): bool
    {
        return (bool)$this->pid;
    }

    private function clearPID(): void
    {
        $this->pid = null;
    }
}
