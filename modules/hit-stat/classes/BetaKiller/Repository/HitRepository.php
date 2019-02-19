<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Hit;
use BetaKiller\Model\HitInterface;
use BetaKiller\Model\HitPage;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class HitRepository
 *
 * @package BetaKiller\Repository
 * @method Hit findById(int $id)
 * @method Hit create()
 * @method Hit[] getAll()
 */
class HitRepository extends AbstractOrmBasedRepository
{
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
            ->limit($orm, 1)
            ->findOne($orm);

    }

    private function filterProcessed(OrmInterface $orm, bool $value): self
    {
        $orm->where(Hit::FIELD_IS_PROCESSED, '=', $value);

        return $this;
    }

    private function orderByCreatedAt(OrmInterface $orm): self
    {
        $orm->order_by(Hit::FIELD_CREATED_AT, 'asc');

        return $this;
    }
}
