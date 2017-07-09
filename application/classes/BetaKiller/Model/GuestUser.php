<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use BetaKiller\Helper\RoleModelFactoryTrait;

class GuestUser extends User
{
    /**
     * @return RoleInterface[]|\Traversable
     */
    public function getAccessControlRoles()
    {
        return [
            $this->get_roles_relation()->get_guest_role(),
        ];
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return 'Guest';
    }
}
