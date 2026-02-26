<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Database;
use DB;

trait SqliteOrmRepositoryTrait
{
    public function __construct(OrmFactory $ormFactory)
    {
        parent::__construct($ormFactory);

        $this->createTableIfNotExists();
    }

    public function delete($entity): void
    {
        parent::delete($entity);

        $this->vacuum();
    }

    protected function deleteAll(OrmInterface $orm): int
    {
        $count = parent::deleteAll($orm);

        $this->vacuum();

        return $count;
    }

    protected function vacuum(): void
    {
        DB::query(Database::SELECT, 'VACUUM')->execute($this->getDatabaseGroup());
    }

    abstract protected function getDatabaseGroup(): string;

    abstract protected function createTableIfNotExists(): void;
}
