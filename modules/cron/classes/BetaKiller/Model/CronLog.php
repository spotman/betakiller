<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

class CronLog extends \ORM implements CronLogInterface
{
    private const RESULT_SUCCEEDED = 'succeeded';
    private const RESULT_FAILED    = 'failed';

    public const COL_NAME       = 'name';
    public const COL_PARAMS     = 'params';
    public const COL_QUEUED_AT  = 'queued_at';
    public const COL_STARTED_AT = 'started_at';
    public const COL_STOPPED_AT = 'stopped_at';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'cron_log';

        $this->serialize_columns([
            self::COL_PARAMS,
        ]);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setName(string $value): CronLogInterface
    {
        $this->set(self::COL_NAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->get(self::COL_NAME);
    }

    /**
     * @param array $value
     *
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function setParams(array $value): CronLogInterface
    {
        $this->set(self::COL_PARAMS, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return (array)$this->get(self::COL_PARAMS);
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
        $this->set_datetime_column_value(self::COL_QUEUED_AT, new DateTimeImmutable);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsStarted(): CronLogInterface
    {
        $this->set_datetime_column_value(self::COL_STARTED_AT, new DateTimeImmutable);

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
        return $this->get_datetime_column_value(self::COL_QUEUED_AT);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_STARTED_AT);
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getStoppedAt(): ?DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_STOPPED_AT);
    }

    private function setStoppedAt(DateTimeImmutable $value): CronLogInterface
    {
        $this->set_datetime_column_value(self::COL_STOPPED_AT, $value);

        return $this;
    }

    private function setResult(bool $value): self
    {
        $result = $value ? self::RESULT_SUCCEEDED : self::RESULT_FAILED;

        $this->set('result', $result);

        return $this;
    }
}
