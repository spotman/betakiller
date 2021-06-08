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
     * @var RoleInterface[]
     */
    private array $roles;

    /**
     * @var string[][]
     */
    private array $pairs;

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
        // Collect tree
        $this->pairs = $this->roleRepo->getChildParentsPairs();

        $this->roles = [];

        // Collect Roles by IDs
        foreach ($this->roleRepo->getAll() as $role) {
            $this->roles[$role->getID()] = $role;
        }

        // Add roles to ACL
        foreach ($this->roles as $role) {
            $this->addRoleWithParents($acl, $role);
        }
    }

    protected function addRoleWithParents(AclInterface $acl, RoleInterface $role): void
    {
        $parentRoles      = $this->getRoleParents($role);
        $parentIdentities = [];

        foreach ($parentRoles as $parentRole) {
            if (!$acl->hasRole($parentRole->getRoleId())) {
                $this->addRoleWithParents($acl, $parentRole);
            }

            $parentIdentities[] = $parentRole->getRoleId();
        }

        if (!$acl->hasRole($role->getRoleId())) {
            $acl->addRole($role->getRoleId(), $parentIdentities);
        }
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return RoleInterface[]
     */
    protected function getRoleParents(RoleInterface $role): array
    {
        return array_map(function ($id) {
            return $this->roles[$id];
        }, $this->pairs[$role->getID()] ?? []);
    }
}
