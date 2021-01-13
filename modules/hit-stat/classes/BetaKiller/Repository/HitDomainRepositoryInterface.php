<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\HitDomainInterface;

/**
 * Class HitDomainRepository
 *
 * @package BetaKiller\Repository
 * @method HitDomainInterface findById(int $id)
 * @method HitDomainInterface[] getAll()
 */
interface HitDomainRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\HitDomainInterface|null
     */
    public function getByName(string $name): ?HitDomainInterface;
}
