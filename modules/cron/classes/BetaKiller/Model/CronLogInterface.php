<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

interface CronLogInterface extends AbstractEntityInterface
{
    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setName(string $value): CronLogInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param array $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setParams(array $value): CronLogInterface;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setCmd(string $value): CronLogInterface;

    /**
     * @return string
     */
    public function getCmd(): string;

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
