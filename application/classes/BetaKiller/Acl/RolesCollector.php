<?php
namespace BetaKiller\Acl;

use Spotman\Acl\Acl;
use Spotman\Acl\RolesCollector\RolesCollectorInterface;
use BetaKiller\Model\RoleInterface;

class RolesCollector implements RolesCollectorInterface
{
    /**
     * @var RoleInterface
     */
    private $roleModel;

    /**
     * RolesCollector constructor.
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
     * @param \Spotman\Acl\Acl $acl
     */
    public function collectRoles(Acl $acl)
    {
        /** @var RoleInterface[] $roles */
        $roles = $this->roleModel->get_all();

        foreach ($roles as $role) {
            $this->addRoleWithParents($acl, $role);
        }
    }

    protected function addRoleWithParents(Acl $acl, RoleInterface $role)
    {
        $parentRoles = $role->getParents();
        $parentRolesIdentities = [];

        foreach ($parentRoles as $parentRole) {
            if (!$acl->hasRole($parentRole)) {
                $this->addRoleWithParents($acl, $parentRole);
            }

            $parentRolesIdentities[] = $parentRole->getRoleId();
        }

        if (!$acl->hasRole($role)) {
            $acl->addRole($role->getRoleId(), $parentRolesIdentities);
        }
    }
}
