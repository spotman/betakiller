<?php
namespace BetaKiller\Model;


class GuestUser extends User
{
    /**
     * @return RoleInterface[]|\Traversable
     */
    public function getAccessControlRoles()
    {
        return [
            new Role(['name' => Role::GUEST_ROLE_NAME]),
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
