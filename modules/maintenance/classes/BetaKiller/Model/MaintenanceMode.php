<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class MaintenanceMode
{
    public const DATE_FORMAT = 'D.M.Y H:I:S';
    /**
     * @var \DateTimeImmutable
     */
    private $startsAt;

    /**
     * @var \DateTimeImmutable
     */
    private $endsAt;

    /**
     * MaintenanceMode constructor.
     *
     * @param \DateTimeImmutable $startsAt
     * @param \DateTimeImmutable $endsAt
     */
    public function __construct(\DateTimeImmutable $startsAt, \DateTimeImmutable $endsAt)
    {
        $this->startsAt = $startsAt;
        $this->endsAt   = $endsAt;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function prolongTill(\DateTimeImmutable $till): void
    {
        $this->endsAt = $till;
    }

    public function isDue(): bool
    {
        return $this->startsAt >= new \DateTimeImmutable;
    }

    public function isFinished(): bool
    {
        return new \DateTimeImmutable >= $this->endsAt;
    }

    public function isInProgress(): bool
    {
        return $this->isDue() && !$this->isFinished();
    }
}
