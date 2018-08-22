<?php
declare(strict_types=1);
namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationException;

class NotificationGroupUserOff extends \ORM implements NotificationGroupUserOffInterface
{
    public const TABLE_NAME           = 'notification_groups_users';
    public const TABLE_FIELD_GROUP_ID = 'group_id';
    public const TABLE_FIELD_USER_ID  = 'user_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'notification_group' => [
                'model'       => 'NotificationGroup',
                'foreign_key' => self::TABLE_FIELD_GROUP_ID,
            ],
        ]);

        $this->load_with(['notification_group']);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            'group_id' => [
                ['not_empty'],
                ['digit'],
            ],
            'user_id'  => [
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
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setGroupId(int $value): NotificationGroupUserOffInterface
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
    public function getUserId(): int
    {
        return (int)$this->get(self::TABLE_FIELD_USER_ID);
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setUserId(int $value): NotificationGroupUserOffInterface
    {
        if ($value < 1) {
            throw new NotificationException('User id most be greater 0');
        }
        $this->set(self::TABLE_FIELD_USER_ID, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupCodename(): string
    {
        return $this->get('notification_group')->getCodename();
    }
}
