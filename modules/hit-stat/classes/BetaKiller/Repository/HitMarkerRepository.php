<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\HitMarker;
use BetaKiller\Model\HitMarkerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

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
        ?string $source,
        ?string $medium,
        ?string $campaign,
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
     * @param string|null                            $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterSource(ExtendedOrmInterface $orm, ?string $value): self
    {
        return $this->filterUtmFieldValue($orm, HitMarker::COL_SOURCE, $value);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string|null                            $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterMedium(ExtendedOrmInterface $orm, ?string $value): self
    {
        return $this->filterUtmFieldValue($orm, HitMarker::COL_MEDIUM, $value);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string|null                            $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterCampaign(ExtendedOrmInterface $orm, ?string $value): self
    {
        return $this->filterUtmFieldValue($orm, HitMarker::COL_CAMPAIGN, $value);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string|null                            $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterContent(ExtendedOrmInterface $orm, ?string $value): self
    {
        return $this->filterUtmFieldValue($orm, HitMarker::COL_CONTENT, $value);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string|null                               $value
     *
     * @return \BetaKiller\Repository\HitMarkerRepository
     */
    private function filterTerm(OrmInterface $orm, ?string $value): self
    {
        return $this->filterUtmFieldValue($orm, HitMarker::COL_TERM, $value);
    }

    protected function filterUtmFieldValue(OrmInterface $orm, string $field, ?string $value): self
    {
        if ($value === null) {
            $orm->where($orm->object_column($field), 'is', null);
        } else {
            $orm->where($orm->object_column($field), '=', $value);
        }

        return $this;
    }
}
