<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use Database;
use DateTimeImmutable;
use DB;

final class CronLog extends \ORM implements CronLogInterface
{
    public const DB_GROUP = 'cron';

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
        $this->_db_group   = self::DB_GROUP;
        $this->_table_name = 'cron_log';

        $this->enableForeignKeys();

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

    private function enableForeignKeys(): void
    {
        if (!self::$tablesChecked) {
            DB::query(Database::SELECT, 'PRAGMA foreign_keys=ON;')->execute($this->_db_group);
            self::$tablesChecked = true;
        }
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
