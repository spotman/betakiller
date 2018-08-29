<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class NotificationGroupUserOff extends \ORM implements NotificationGroupUserOffInterface
{
    public const TABLE_NAME           = 'notification_groups_users_off';
    public const TABLE_FIELD_GROUP_ID = 'group_id';
    public const TABLE_FIELD_USER_ID  = 'user_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'groups' => [
                'model'       => 'NotificationGroup',
                'foreign_key' => 'group_id',
            ],
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_GROUP_ID => [
                ['not_empty'],
            ],
            self::TABLE_FIELD_USER_ID  => [
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
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
     */
    public function setGroupId(int $value): NotificationGroupUserOffInterface
    {
        $this->set(self::TABLE_FIELD_GROUP_ID, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->get(self::TABLE_FIELD_USER_ID);
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
     */
    public function setUserId(int $value): NotificationGroupUserOffInterface
    {
        $this->set(self::TABLE_FIELD_USER_ID, $value);

        return $this;
    }
}
