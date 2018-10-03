<?php
namespace BetaKiller\Acl;

use BetaKiller\Model\AclRule;
use Spotman\Acl\AclInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;

class AclRulesCollector implements AclRulesCollectorInterface
{
    /**
     * @var AclRule
     */
    private $permissionModel;

    /**
     * AclRulesCollector constructor.
     *
     * @param AclRule $permissionModel
     */
    public function __construct(AclRule $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    /**
     * Collect entities from external source and add them to acl via protected methods addAllowRule / addDenyRule
     *
     * @param \Spotman\Acl\AclInterface $acl
     *
     * @throws \Kohana_Exception
     */
    public function collectPermissions(AclInterface $acl): void
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
