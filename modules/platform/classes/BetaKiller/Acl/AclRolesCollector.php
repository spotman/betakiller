<?php
namespace BetaKiller\Acl;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use Spotman\Acl\AclInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;

class AclRolesCollector implements AclRolesCollectorInterface
{
    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private $roleRepo;

    /**
     * AclRolesCollector constructor.
     *
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     */
    public function __construct(RoleRepositoryInterface $roleRepo)
    {
        $this->roleRepo = $roleRepo;
    }

    /**
     * Collect roles from external source and add them to acl via protected methods addRole / removeRole
     *
     * @param \Spotman\Acl\AclInterface $acl
     */
    public function collectRoles(AclInterface $acl): void
    {
        foreach ($this->roleRepo->getAll() as $role) {
            $this->addRoleWithParents($acl, $role);
        }
    }

    protected function addRoleWithParents(AclInterface $acl, RoleInterface $role): void
    {
        /** @var RoleInterface[] $parentRoles */
        $parentRoles           = $role->getParents();
        $parentRolesIdentities = [];

        foreach ($parentRoles as $parentRole) {
            if (!$acl->hasRole($parentRole->getRoleId())) {
                $this->addRoleWithParents($acl, $parentRole);
            }

            $parentRolesIdentities[] = $parentRole->getRoleId();
        }

        if (!$acl->hasRole($role->getRoleId())) {
            $acl->addRole($role->getRoleId(), $parentRolesIdentities);
        }
    }
}
