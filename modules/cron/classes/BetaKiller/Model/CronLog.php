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

    public const COL_COMMAND_ID = 'command_id';
    public const COL_QUEUED_AT  = 'queued_at';
    public const COL_STARTED_AT = 'started_at';
    public const COL_STOPPED_AT = 'stopped_at';
    public const COL_RESULT     = 'result';

    public const REL_COMMAND = 'command';

    private static bool $tablesChecked = false;

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_db_group   = 'cron';
        $this->_table_name = 'cron_log';

        $this->createTablesIfNotExists();

        $this->belongs_to([
            self::REL_COMMAND => [
                'model'       => CronCommand::getModelName(),
                'foreign_key' => self::COL_COMMAND_ID,
            ],
        ]);

        // PDO exceptions are raised when this block is enabled
//        $this->load_with([
//            self::REL_COMMAND,
//        ]);
    }

    private function createTablesIfNotExists(): void
    {
        if (!static::$tablesChecked) {
            $this->enableAutoVacuum();
            $this->enableForeignKeys();
            $this->createCronLogTableIfNotExists();
            static::$tablesChecked = true;
        }
    }

    private function createCronLogTableIfNotExists(): void
    {
        \DB::query(\Database::SELECT, 'CREATE TABLE IF NOT EXISTS `cron_log` (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            command_id INTEGER NOT NULL,
            result VARCHAR(10) CHECK( result IN ("started","queued","succeeded","failed") ) NOT NULL,
            queued_at DATETIME NOT NULL,
            started_at DATETIME DEFAULT NULL,
            stopped_at DATETIME DEFAULT NULL,
            FOREIGN KEY(command_id) REFERENCES cron_commands(id)
        );')->execute($this->_db_group);
    }

    private function enableForeignKeys(): void
    {
        \DB::query(\Database::SELECT, 'PRAGMA foreign_keys=ON;')->execute($this->_db_group);
    }

    private function enableAutoVacuum(): void
    {
        \DB::query(\Database::SELECT, 'PRAGMA auto_vacuum = FULL')->execute($this->_db_group);
    }

    /**
     * @inheritDoc
     */
    public function setCommand(CronCommandInterface $cmd): CronLogInterface
    {
        $this->setOnce(self::REL_COMMAND, $cmd);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCommand(): CronCommandInterface
    {
        return $this->getRelatedEntity(self::REL_COMMAND);
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
        $this->set(self::COL_RESULT, $value);

        return $this;
    }
}
