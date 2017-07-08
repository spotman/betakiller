<?php
namespace BetaKiller\Repository;

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
class RoleRepository extends AbstractOrmBasedRepository
{
    public function getGuestRole(): RoleInterface
    {
        return $this->getOrmInstance()->get_guest_role();
    }

    public function getLoginRole(): RoleInterface
    {
        return $this->getOrmInstance()->get_login_role();
    }

    public function getModeratorRole(): RoleInterface
    {
        return $this->getOrmInstance()->get_moderator_role();
    }

    public function getDeveloperRole(): RoleInterface
    {
        return $this->getOrmInstance()->get_developer_role();
    }
}
