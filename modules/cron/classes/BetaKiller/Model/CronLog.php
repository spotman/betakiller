<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

class CronLog extends \ORM implements CronLogInterface
{
    private const RESULT_SUCCEEDED = 'succeeded';
    private const RESULT_FAILED    = 'failed';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'cron_log';
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setName(string $value): CronLogInterface
    {
        $this->set('name', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->get('name');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setCmd(string $value): CronLogInterface
    {
        $this->set('cmd', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return (string)$this->get('cmd');
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsQueued(): CronLogInterface
    {
        $this->set_datetime_column_value('queued_at', new DateTimeImmutable);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsStarted(): CronLogInterface
    {
        $this->set_datetime_column_value('started_at', new DateTimeImmutable);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsSucceeded(): CronLogInterface
    {
        return $this
            ->setResult(true)
            ->setStoppedAt(new DateTimeImmutable);
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsFailed(): CronLogInterface
    {
        return $this
            ->setResult(false)
            ->setStoppedAt(new DateTimeImmutable);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getQueuedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('queued_at');
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('started_at');
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getStoppedAt(): ?DateTimeImmutable
    {
        return $this->get_datetime_column_value('stopped_at');
    }

    private function setStoppedAt(DateTimeImmutable $value): CronLogInterface
    {
        $this->set_datetime_column_value('stopped_at', $value);

        return $this;
    }

    private function setResult(bool $value): self
    {
        $result = $value ? self::RESULT_SUCCEEDED : self::RESULT_FAILED;

        $this->set('result', $result);

        return $this;
    }
}
