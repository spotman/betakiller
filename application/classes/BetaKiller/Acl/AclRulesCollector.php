<?php
namespace BetaKiller\Acl;

use Model_AclRule;
use Spotman\Acl\AclInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;

class AclRulesCollector implements AclRulesCollectorInterface
{
    /**
     * @var Model_AclRule
     */
    private $permissionModel;

    /**
     * AclRulesCollector constructor.
     *
     * @param Model_AclRule $permissionModel
     */
    public function __construct(Model_AclRule $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    /**
     * Collect entities from external source and add them to acl via protected methods addAllowRule / addDenyRule
     *
     * @param \Spotman\Acl\AclInterface $acl
     */
    public function collectPermissions(AclInterface $acl)
    {
        $permissions = $this->permissionModel->getAllPermissions();

        foreach ($permissions as $permission) {
            $role                     = $permission->getAclRoleIdentity();
            $resourceIdentity         = $permission->getAclResourceIdentity();
            $actionPermissionIdentity = $permission->getAclActionIdentity();

            $value = $permission->isAllowed();

            if ($value === true) {
                $acl->addAllowRule($role, $resourceIdentity, $actionPermissionIdentity);
            } elseif ($value === false) {
                $acl->addDenyRule($role, $resourceIdentity, $actionPermissionIdentity);
            }
        }
    }
}
