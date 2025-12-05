<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CronCommandInterface;
use BetaKiller\Model\CronLog;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class CronLogRepository extends AbstractOrmBasedRepository implements CronLogRepositoryInterface
{
    use SqliteOrmRepositoryTrait;

    protected function getDatabaseGroup(): string
    {
        return CronLog::DB_GROUP;
    }

    protected function createTableIfNotExists(): void
    {
        \DB::query(\Database::SELECT, 'CREATE TABLE IF NOT EXISTS `cron_log` (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            command_id INTEGER NOT NULL,
            result VARCHAR(10) CHECK( result IN ("started","queued","succeeded","failed") ) NOT NULL,
            queued_at DATETIME NOT NULL,
            started_at DATETIME DEFAULT NULL,
            stopped_at DATETIME DEFAULT NULL,
            FOREIGN KEY(command_id) REFERENCES cron_commands(id)
        );')->execute($this->getDatabaseGroup());
    }

    /**
     * @inheritDoc
     */
    public function removeRecordsOlderThan(\DateTimeImmutable $beforeDate): void
    {
        $orm = $this->getOrmInstance();

        $orm->filter_datetime_column_value(CronLog::COL_QUEUED_AT, $beforeDate, '<');

        $this->deleteAll($orm);
    }

    /**
     * @inheritDoc
     */
    public function hasTaskRecordAfter(CronCommandInterface $cmd, \DateTimeImmutable $afterDate): bool
    {
        $orm = $this->getOrmInstance();

        $orm->filter_datetime_column_value(CronLog::COL_QUEUED_AT, $afterDate, '>=');

        return $this
            ->filterCmd($orm, $cmd)
            ->countAll($orm) > 0;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param \BetaKiller\Model\CronCommandInterface    $command
     *
     * @return $this
     */
    private function filterCmd(OrmInterface $orm, CronCommandInterface $command): self
    {
        $orm->where($orm->object_column(CronLog::COL_COMMAND_ID), '=', $command->getID());

        return $this;
    }
}
