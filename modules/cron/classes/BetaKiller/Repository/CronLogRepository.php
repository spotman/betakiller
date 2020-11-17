<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CronCommandInterface;
use BetaKiller\Model\CronLog;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class CronLogRepository extends AbstractOrmBasedRepository implements CronLogRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function removeRecordsOlderThan(\DateTimeImmutable $beforeDate): void
    {
        $orm = $this->getOrmInstance();

        $orm->filter_datetime_column_value(CronLog::COL_QUEUED_AT, $beforeDate, '<');

        foreach ($this->findAll($orm) as $record) {
            $this->delete($record);
        }
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
