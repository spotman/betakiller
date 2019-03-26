<?php
namespace BetaKiller\Repository;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\HitDomain;
use BetaKiller\Model\HitPage;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class HitPageRepository
 *
 * @package BetaKiller\Repository
 * @method HitPage findById(int $id)
 * @method HitPage[] getAll()
 * @method void save(HitPage $entity)
 */
class HitPageRepository extends AbstractOrmBasedRepository
{
    public function findByUri(HitDomain $domain, string $uri): ?HitPage
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterDomain($orm, $domain)
            ->filterUri($orm, $uri)
            ->findOne($orm);
    }

    private function filterDomain(OrmInterface $orm, HitDomain $domain): self
    {
        $this->filterRelated($orm, HitPage::RELATION_DOMAIN, $domain);

        return $this;
    }

    private function filterUri(OrmInterface $orm, string $uri): self
    {
        $orm->where('uri', '=', $uri);

        return $this;
    }
}
