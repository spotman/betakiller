<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

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
     * @param string             $taskName
     * @param array              $params
     * @param \DateTimeImmutable $afterDate
     *
     * @return bool
     */
    public function hasTaskRecordAfter(string $taskName, array $params, \DateTimeImmutable $afterDate): bool
    {
        $orm = $this->getOrmInstance();

        $orm->filter_datetime_column_value(CronLog::COL_QUEUED_AT, $afterDate, '>=');

        $this
            ->filterName($orm, $taskName)
            ->filterParams($orm, $params);

        return $this->countAll($orm) > 0;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $name
     *
     * @return $this
     */
    private function filterName(OrmInterface $orm, string $name): self
    {
        $orm->where($orm->object_column(CronLog::COL_NAME), '=', $name);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param array                                     $params
     *
     * @return $this
     */
    private function filterParams(OrmInterface $orm, array $params): self
    {
        $orm->where($orm->object_column(CronLog::COL_PARAMS), '=', \json_encode($params));

        return $this;
    }
}
