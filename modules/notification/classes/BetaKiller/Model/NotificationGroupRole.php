<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class NotificationGroupRole extends \ORM implements NotificationGroupRoleInterface
{
    public const TABLE_NAME           = 'notification_groups_roles';
    public const TABLE_FIELD_GROUP_ID = 'group_id';
    public const TABLE_FIELD_ROLE_ID  = 'role_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_GROUP_ID => [
                ['not_empty'],
            ],
            self::TABLE_FIELD_ROLE_ID  => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->get(self::TABLE_FIELD_GROUP_ID);
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     */
    public function setGroupId(int $value): NotificationGroupRoleInterface
    {
        $this->set(self::TABLE_FIELD_GROUP_ID, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return $this->get(self::TABLE_FIELD_ROLE_ID);
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     */
    public function setRoleId(int $value): NotificationGroupRoleInterface
    {
        $this->set(self::TABLE_FIELD_ROLE_ID, $value);

        return $this;
    }
}
