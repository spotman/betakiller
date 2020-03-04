<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Hit;
use BetaKiller\Model\HitInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Class HitRepository
 *
 * @package BetaKiller\Repository
 * @method Hit findById(int $id)
 * @method Hit[] getAll()
 */
class HitRepository extends AbstractOrmBasedRepository
{
    /**
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return \BetaKiller\Model\HitInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByUuid(UuidInterface $uuid): HitInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUuid($orm, $uuid)
            ->getOne($orm);
    }

    /**
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return \BetaKiller\Model\HitInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByUuid(UuidInterface $uuid): ?HitInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUuid($orm, $uuid)
            ->findOne($orm);
    }

    /**
     * @param int $limit
     *
     * @return Hit[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPending(int $limit): array
    {
        $limit = $limit ?? 100;

        $orm = $this->getOrmInstance();

        if ($limit) {
            $orm->limit($limit);
        }

        return $this
            ->filterProcessed($orm, false)
            ->findAll($orm);
    }

    public function getFirstNotProcessed(): ?HitInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterProcessed($orm, false)
            ->orderByCreatedAt($orm)
            ->findOne($orm);
    }

    private function filterProcessed(OrmInterface $orm, bool $value): self
    {
        $orm->where(Hit::COL_IS_PROCESSED, '=', $value);

        return $this;
    }

    private function filterUuid(OrmInterface $orm, UuidInterface $uuid): self
    {
        $orm->where(Hit::COL_UUID, '=', $uuid->toString());

        return $this;
    }

    private function orderByCreatedAt(OrmInterface $orm): self
    {
        $orm->order_by(Hit::COL_CREATED_AT, 'asc');

        return $this;
    }
}
