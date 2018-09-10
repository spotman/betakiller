<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class GuestUser extends User
{
    /**
     * @return RoleInterface[]
     * @throws \Kohana_Exception
     */
    public function getAccessControlRoles(): array
    {
        return [
            new Role(['name' => RoleInterface::GUEST_ROLE_NAME]),
        ];
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return 'Guest';
    }

    protected function fetchAllUserRolesNames(): array
    {
        return [
            RoleInterface::GUEST_ROLE_NAME
        ];
    }
}
