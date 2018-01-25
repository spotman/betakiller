<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\Role;
use BetaKiller\Model\RoleInterface;

/**
 * Class UserRepository
 *
 * @package BetaKiller\Repository
 * @method RoleInterface create()
 * @method RoleInterface findById(int $id)
 * @method RoleInterface[] getAll()
 * @method Role getOrmInstance()
 */
class RoleRepository extends AbstractOrmBasedMultipleParentsTreeRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return RoleInterface::URL_KEY;
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getGuestRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::GUEST_ROLE_NAME);
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getLoginRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::LOGIN_ROLE_NAME);
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getModeratorRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::MODERATOR_ROLE_NAME);
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getDeveloperRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::DEVELOPER_ROLE_NAME);
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByName(string $name): RoleInterface
    {
        $orm = $this->getOrmInstance();

        $this->filterName($orm, $name);

        return $this->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $name
     *
     * @return \BetaKiller\Repository\RoleRepository
     */
    private function filterName(ExtendedOrmInterface $orm, string $name): RoleRepository
    {
        $orm->where($orm->object_column('name'), '=', $name);

        return $this;
    }
}
