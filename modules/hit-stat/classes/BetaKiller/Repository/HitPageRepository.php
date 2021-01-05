<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\HitDomain;
use BetaKiller\Model\HitPage;
use BetaKiller\Model\HitPageInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class HitPageRepository
 *
 * @package BetaKiller\Repository
 * @method HitPageInterface findById(int $id)
 * @method HitPageInterface[] getAll()
 * @method void save(HitPageInterface $entity)
 */
final class HitPageRepository extends AbstractOrmBasedRepository implements HitPageRepositoryInterface
{
    public function findByUri(HitDomain $domain, string $uri): ?HitPageInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterDomain($orm, $domain)
            ->filterUri($orm, $uri)
            ->findOne($orm);
    }

    private function filterDomain(OrmInterface $orm, HitDomain $domain): self
    {
        $this->filterRelated($orm, HitPage::REL_DOMAIN, $domain);

        return $this;
    }

    private function filterUri(OrmInterface $orm, string $uri): self
    {
        $orm->where('uri', '=', $uri);

        return $this;
    }
}
