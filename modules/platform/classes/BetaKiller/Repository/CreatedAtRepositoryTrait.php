<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;

trait CreatedAtRepositoryTrait
{
    /**
     * @param OrmInterface $orm
     * @param bool|null    $asc
     *
     * @return self
     */
    protected function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null): self
    {
        $orm->order_by($orm->object_column($this->getCreatedAtColumnName()), ($asc ?? false) ? 'ASC' : 'DESC');

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param \DateTimeImmutable                        $from
     * @param \DateTimeImmutable                        $to
     *
     * @return $this
     */
    protected function filterCreatedAtBetween(OrmInterface $orm, \DateTimeImmutable $from, \DateTimeImmutable $to): self
    {
        $col = $orm->object_column($this->getCreatedAtColumnName());

        $orm->filter_datetime_column_value_between($col, $from, $to);

        return $this;
    }

    protected function filterCreatedAtBefore(OrmInterface $orm, \DateTimeImmutable $before): self
    {
        $col = $orm->object_column($this->getCreatedAtColumnName());

        $orm->filter_datetime_column_value($col, $before, '<');

        return $this;
    }

    protected function filterCreatedAtAfter(OrmInterface $orm, \DateTimeImmutable $after): self
    {
        $col = $orm->object_column($this->getCreatedAtColumnName());

        $orm->filter_datetime_column_value($col, $after, '>');

        return $this;
    }

    protected function filterCreatedOn(OrmInterface $orm, \DateTimeImmutable $date): self
    {
        $from = $date->setTime(0, 0);
        $to   = $date->setTime(23, 59, 59);

        return $this->filterCreatedAtBetween($orm, $from, $to);
    }

    abstract protected function getCreatedAtColumnName(): string;
}
