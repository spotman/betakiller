<?php
declare(strict_types=1);
namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationException;

class NotificationGroupRole extends \ORM implements NotificationGroupRoleInterface
{
    public const TABLE_NAME           = 'notification_group_role';
    public const TABLE_FIELD_GROUP_ID = 'group_id';
    public const TABLE_FIELD_ROLE_ID  = 'role_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'notification_group' => [
                'model'       => 'NotificationGroup',
                'foreign_key' => self::TABLE_FIELD_GROUP_ID,
            ],
            'roles'               => [
                'model'       => 'Role',
                'foreign_key' => self::TABLE_FIELD_GROUP_ID,
            ],
        ]);

        $this->load_with(['notification_group','roles']);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            'group_id' => [
                ['not_empty'],
                ['digit'],
            ],
            'role_id'  => [
                ['not_empty'],
                ['digit'],
            ],
        ];
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return (int)$this->get(self::TABLE_FIELD_GROUP_ID);
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setGroupId(int $value): NotificationGroupRoleInterface
    {
        if ($value < 1) {
            throw new NotificationException('Group id most be greater 0');
        }
        $this->set(self::TABLE_FIELD_GROUP_ID, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return (int)$this->get(self::TABLE_FIELD_ROLE_ID);
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setRoleId(int $value): NotificationGroupRoleInterface
    {
        if ($value < 1) {
            throw new NotificationException('Role id most be greater 0');
        }
        $this->set(self::TABLE_FIELD_ROLE_ID, $value);

        return $this;
    }

    /**
     * @return array[string self::TABLE_FIELD_GROUP_ID, string self::TABLE_FIELD_ROLE_ID]
     */
    public function getAll(): array
    {
        return [
            self::TABLE_FIELD_GROUP_ID => $this->getGroupId(),
            self::TABLE_FIELD_ROLE_ID  => $this->getRoleId(),
        ];
    }

    /**
     * @return string
     */
    public function getGroupCodename(): string
    {
        return $this->get('notification_group')->getCodename();
    }

    /**
     * @return string
     */
    public function getRoleCodename(): string
    {
        return $this->get('roles')->getName();
    }
}
