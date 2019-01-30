<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\HitMarker;
use BetaKiller\Model\HitMarkerInterface;

/**
 * Class HitMarkerRepository
 *
 * @package BetaKiller\Repository
 * @method HitMarkerInterface findById(int $id)
 * @method HitMarkerInterface[] getAll()
 * @method void save(HitMarkerInterface $entity)
 */
class HitMarkerRepository extends AbstractOrmBasedRepository
{
    public function find(
        string $source,
        string $medium,
        string $campaign,
        ?string $content,
        ?string $term
    ): ?HitMarkerInterface {
        $orm = $this->getOrmInstance();

        return $this
            ->filterSource($orm, $source)
            ->filterMedium($orm, $medium)
            ->filterCampaign($orm, $campaign)
            ->filterContent($orm, $content)
            ->filterTerm($orm, $term)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterSource(ExtendedOrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(HitMarker::FIELD_SOURCE), '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterMedium(ExtendedOrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(HitMarker::FIELD_MEDIUM), '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterCampaign(ExtendedOrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(HitMarker::FIELD_CAMPAIGN), '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterContent(ExtendedOrmInterface $orm, ?string $value): self
    {
        if ($value === null) {
            $orm->where($orm->object_column(HitMarker::FIELD_CONTENT), 'is', null);
        } else {
            $orm->where($orm->object_column(HitMarker::FIELD_CONTENT), '=', $value);
        }

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterTerm(ExtendedOrmInterface $orm, ?string $value): self
    {
        if ($value === null) {
            $orm->where($orm->object_column(HitMarker::FIELD_TERM), 'is', null);
        } else {
            $orm->where($orm->object_column(HitMarker::FIELD_TERM), '=', $value);
        }

        return $this;
    }
}
