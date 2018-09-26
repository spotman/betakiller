<?php
namespace BetaKiller\Model;

class AclRule extends \ORM
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'acl_rules';

        $this->belongs_to([
            'role' => [
                'model'       => 'Role',
                'foreign_key' => 'role_id',
            ],

            'resource' => [
                'model'       => 'AclResource',
                'foreign_key' => 'resource_id',
            ],

            'permission' => [
                'model'       => 'AclResourcePermission',
                'foreign_key' => 'permission_id',
            ],
        ]);

        $this->load_with(['resource', 'role', 'permission']);
    }

    /**
     * @return string
     */
    public function getAclActionIdentity(): string
    {
        return $this->getPermissionRelation()->getName();
    }

    /**
     * @return AclResourcePermission
     */
    private function getPermissionRelation(): AclResourcePermission
    {
        return $this->get('permission');
    }

    /**
     * Null means "inherit", true - enabled, false - disabled
     *
     * @return bool|null
     */
    public function isAllowed(): ?bool
    {
        $value = $this->get('is_allowed');

        return ($value === null) ? null : (bool)$value;
    }

    /**
     * @return string
     */
    public function getAclRoleIdentity(): string
    {
        return $this->getRoleRelation()->getName();
    }

    /**
     * @return string
     */
    public function getAclResourceIdentity(): string
    {
        return $this->getResourceRelation()->getCodename();
    }

    /**
     * @return AclRule[]
     */
    public function getAllPermissions(): array
    {
        return $this->get_all();
    }

    /**
     * @return \BetaKiller\Model\Role
     */
    private function getRoleRelation(): Role
    {
        return $this->get('role');
    }

    /**
     * @return \BetaKiller\Model\AclResource
     */
    private function getResourceRelation(): AclResource
    {
        return $this->get('resource');
    }
}
