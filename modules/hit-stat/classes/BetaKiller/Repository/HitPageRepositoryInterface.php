<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\HitDomain;
use BetaKiller\Model\HitPageInterface;

/**
 * Class HitPageRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method HitPageInterface getById(string $id)
 * @method HitPageInterface findById(string $id)
 * @method HitPageInterface[] getAll()
 * @method void save(HitPageInterface $entity)
 */
interface HitPageRepositoryInterface extends RepositoryInterface
{
    public function findByUri(HitDomain $domain, string $uri): ?HitPageInterface;
}
