<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\HitLink;
use BetaKiller\Model\HitPage;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class HitLinkRepository
 *
 * @package BetaKiller\Repository
 * @method HitLink findById(int $id)
 * @method HitLink create()
 * @method HitLink[] getAll()
 */
class HitLinkRepository extends AbstractOrmBasedRepository
{
    /**
     * @param \BetaKiller\Model\HitPage $source
     * @param \BetaKiller\Model\HitPage $target
     *
     * @return \BetaKiller\Model\HitLink|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findBySourceAndTarget(HitPage $source, HitPage $target): ?HitLink
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterSource($orm, $source)
            ->filterTarget($orm, $target)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param \BetaKiller\Model\HitPage                 $source
     *
     * @return \BetaKiller\Repository\HitLinkRepository
     */
    private function filterSource(OrmInterface $orm, HitPage $source): self
    {
        $orm->where('source_id', '=', $source->getID());

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param \BetaKiller\Model\HitPage                 $target
     *
     * @return \BetaKiller\Repository\HitLinkRepository
     */
    private function filterTarget(OrmInterface $orm, HitPage $target): self
    {
        $orm->where('target_id', '=', $target->getID());

        return $this;
    }
}
