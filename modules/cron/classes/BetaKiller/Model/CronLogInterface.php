<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

interface CronLogInterface extends AbstractEntityInterface
{
    /**
     * @param \BetaKiller\Model\CronCommandInterface $cmd
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setCommand(CronCommandInterface $cmd): CronLogInterface;

    /**
     * @return CronCommandInterface
     */
    public function getCommand(): CronCommandInterface;

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsQueued(): CronLogInterface;

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsStarted(): CronLogInterface;

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsSucceeded(): CronLogInterface;

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsFailed(): CronLogInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getQueuedAt(): DateTimeImmutable;

    /**
     * @return \DateTimeImmutable
     */
    public function getStartedAt(): DateTimeImmutable;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getStoppedAt(): ?DateTimeImmutable;
}
