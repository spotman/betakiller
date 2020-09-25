<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

final class CronLog extends \ORM implements CronLogInterface
{
    private const RESULT_QUEUED    = 'queued';
    private const RESULT_STARTED   = 'started';
    private const RESULT_SUCCEEDED = 'succeeded';
    private const RESULT_FAILED    = 'failed';

    public const COL_NAME       = 'name';
    public const COL_PARAMS     = 'params';
    public const COL_QUEUED_AT  = 'queued_at';
    public const COL_STARTED_AT = 'started_at';
    public const COL_STOPPED_AT = 'stopped_at';

    private static bool $tablesChecked = false;

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_db_group   = 'cron';
        $this->_table_name = 'cron_log';

        $this->createTablesIfNotExists();

        $this->serialize_columns([
            self::COL_PARAMS,
        ]);
    }

    private function createTablesIfNotExists(): void
    {
        if (!static::$tablesChecked) {
            $this->enableAutoVacuum();
            $this->createCronLogTableIfNotExists();
            static::$tablesChecked = true;
        }
    }

    private function createCronLogTableIfNotExists(): void
    {
        \DB::query(\Database::SELECT, 'CREATE TABLE IF NOT EXISTS `cron_log` (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(32) NOT NULL,
            params VARCHAR(255) NOT NULL,
            cmd VARCHAR(255) NOT NULL,
            result VARCHAR(10) CHECK( result IN ("started","queued","succeeded","failed") ) NOT NULL,
            queued_at DATETIME NOT NULL,
            started_at DATETIME DEFAULT NULL,
            stopped_at DATETIME DEFAULT NULL
        );')->execute($this->_db_group);
    }

    private function enableAutoVacuum(): void
    {
        \DB::query(\Database::SELECT, 'PRAGMA auto_vacuum = FULL')->execute($this->_db_group);
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
        $this
            ->set_datetime_column_value(self::COL_QUEUED_AT, new DateTimeImmutable)
            ->setResult(self::RESULT_QUEUED);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsStarted(): CronLogInterface
    {
        $this
            ->set_datetime_column_value(self::COL_STARTED_AT, new DateTimeImmutable)
            ->setResult(self::RESULT_STARTED);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsSucceeded(): CronLogInterface
    {
        return $this
            ->setResult(self::RESULT_SUCCEEDED)
            ->setStoppedAt(new DateTimeImmutable);
    }

    /**
     * @return \BetaKiller\Model\CronLogInterface
     */
    public function markAsFailed(): CronLogInterface
    {
        return $this
            ->setResult(self::RESULT_FAILED)
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

    private function setResult(string $value): self
    {
        $this->set('result', $value);

        return $this;
    }
}
