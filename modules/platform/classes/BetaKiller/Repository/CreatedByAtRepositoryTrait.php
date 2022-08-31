<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\UserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

trait CreatedByAtRepositoryTrait
{
    use CreatedAtRepositoryTrait;

    public function migrateBetweenUsers(UserInterface $from, UserInterface $to): void
    {
        $this->migrateUsersInColumn($from, $to, $this->getCreatedByColumnName());
    }

    protected function migrateUsersInColumn(UserInterface $from, UserInterface $to, string $column): void
    {
        \DB::update($this->getTableName())
            ->where($column, '=', $from->getID())
            ->set([
                $column => $to->getID(),
            ])
            ->execute();
    }

    /**
     * @param OrmInterface                    $orm
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return self
     */
    protected function filterCreatedBy(OrmInterface $orm, UserInterface $user): self
    {
        $orm->where($orm->object_column($this->getCreatedByColumnName()), '=', $user->getID());

        return $this;
    }

    abstract protected function getTableName(): string;

    abstract protected function getCreatedByColumnName(): string;
}
