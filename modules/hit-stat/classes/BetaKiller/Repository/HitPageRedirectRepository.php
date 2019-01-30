<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\HitPageRedirectInterface;

/**
 * Class HitPageRedirectRepository
 *
 * @package BetaKiller\Repository
 * @method HitPageRedirectInterface create()
 */
class HitPageRedirectRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return HitPageRedirectInterface::URL_KEY;
    }

    /**
     * @param string $url
     *
     * @return \BetaKiller\Model\HitPageRedirectInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByUrl(string $url): ?HitPageRedirectInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUrl($orm, $url)
            ->findOne($orm);
    }

    private function filterUrl(ExtendedOrmInterface $orm, string $url): self
    {
        $orm->where($orm->object_column('url'), '=', $url);

        return $this;
    }
}
