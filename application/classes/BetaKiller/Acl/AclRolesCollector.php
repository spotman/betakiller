<?php
namespace BetaKiller\Acl;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\RoleRepository;
use Spotman\Acl\AclInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;

class AclRolesCollector implements AclRolesCollectorInterface
{
    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepo;

    /**
     * AclRolesCollector constructor.
     *
     * @param \BetaKiller\Repository\RoleRepository $roleRepo
     */
    public function __construct(RoleRepository $roleRepo)
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
        $roles = $this->roleRepo->getAll();

        foreach ($roles as $role) {
            $this->addRoleWithParents($acl, $role);
        }
    }

    protected function addRoleWithParents(AclInterface $acl, RoleInterface $role)
    {
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
