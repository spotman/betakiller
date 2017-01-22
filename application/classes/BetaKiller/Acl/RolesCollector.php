<?php
namespace BetaKiller\Acl;

use Spotman\Acl\RolesCollector\AbstractRolesCollector;
use BetaKiller\Model\RoleInterface;

class RolesCollector extends AbstractRolesCollector
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
     */
    public function collectRoles()
    {
        /** @var RoleInterface $roles */
        $roles = $this->roleModel->find_all();

        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }
}
