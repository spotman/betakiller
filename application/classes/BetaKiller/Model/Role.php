<?php
namespace BetaKiller\Model;

class Role extends AbstractOrmBasedMultipleParentsTreeModel implements RoleInterface
{
    protected function configure(): void
    {
        $this->has_many([
            'notification_groups' => [
                'model'       => 'NotificationGroupRole',
                'foreign_key' => NotificationGroupRole::TABLE_FIELD_ROLE_ID,
            ],
        ]);

        $this->load_with(['notification_groups']);

        parent::configure();
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     */
    protected function getNotificationGroupsRelation(): NotificationGroupRoleInterface
    {
        return $this->get('notification_groups');
    }

    /**
     * @return NotificationGroupRoleInterface[]|\Traversable
     */
    public function getNotificationGroups()
    {
        return $this->getNotificationGroupsRelation()->get_all();
    }

    protected function getTreeModelThroughTableName()
    {
        return 'roles_inheritance';
    }

    public function rules()
    {
        return [
            'name'        => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            'description' => [
                ['max_length', [':value', 255]],
            ],
        ];
    }

    public function getName(): string
    {
        return $this->get('name');
    }

    /**
     * Returns the string identifier of the Role
     *
     * @return string
     */
    public function getRoleId(): string
    {
        return $this->getName();
    }
}
