<?php
namespace BetaKiller\Acl;

use BetaKiller\Model\RoleInterface;
use Spotman\Acl\AclInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;

class AclRolesCollector implements AclRolesCollectorInterface
{
    /**
     * @var RoleInterface
     */
    private $roleModel;

    /**
     * AclRolesCollector constructor.
     *
     * @param \BetaKiller\Model\RoleInterface $roleModel
     */
    public function __construct(RoleInterface $roleModel)
    {
        $this->roleModel = $roleModel;
    }

    /**
     * Collect roles from external source and add them to acl via protected methods addRole / removeRole
     *
     * @param \Spotman\Acl\AclInterface $acl
     */
    public function collectRoles(AclInterface $acl)
    {
        /** @var RoleInterface[] $roles */
        $roles = $this->roleModel->get_all();

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
