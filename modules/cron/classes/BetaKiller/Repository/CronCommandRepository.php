<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CronCommand;
use BetaKiller\Model\CronCommandInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

use function json_encode;

final class CronCommandRepository extends AbstractOrmBasedRepository implements CronCommandRepositoryInterface
{
    use SqliteOrmRepositoryTrait;

    protected function getDatabaseGroup(): string
    {
        return CronCommand::DB_GROUP;
    }

    protected function createTableIfNotExists(): void
    {
        \DB::query(
            \Database::SELECT,
            'CREATE TABLE IF NOT EXISTS `cron_commands` (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(32) NOT NULL,
            params VARCHAR(255) NULL DEFAULT NULL,
            cmd VARCHAR(255) UNIQUE NOT NULL
        );'
        )->execute($this->getDatabaseGroup());
    }

    /**
     * @inheritDoc
     */
    public function findByNameAndParams(string $taskName, array $params): ?CronCommandInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterName($orm, $taskName)
            ->filterParams($orm, $params)
            ->findOne($orm);
    }

    /**
     * @inheritDoc
     */
    public function findByCmd(string $cmd): ?CronCommandInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCmd($orm, $cmd)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $name
     *
     * @return $this
     */
    private function filterName(OrmInterface $orm, string $name): self
    {
        $orm->where($orm->object_column(CronCommand::COL_NAME), '=', $name);

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
        $filter = $params
            ? json_encode($params, JSON_THROW_ON_ERROR)
            : null;

        $orm->where($orm->object_column(CronCommand::COL_PARAMS), '=', $filter);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $cmd
     *
     * @return $this
     */
    private function filterCmd(OrmInterface $orm, string $cmd): self
    {
        $orm->where($orm->object_column(CronCommand::COL_CMD), '=', $cmd);

        return $this;
    }
}
