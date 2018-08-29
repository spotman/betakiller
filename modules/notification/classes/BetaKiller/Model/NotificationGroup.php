<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class NotificationGroup extends \ORM implements NotificationGroupInterface
{
    public const TABLE_NAME              = 'notification_groups';
    public const TABLE_FIELD_CODENAME    = 'codename';
    public const TABLE_FIELD_DESCRIPTION = 'description';

    public const USERS_OFF_TABLE_NAME           = 'notification_groups_users_off';
    public const USERS_OFF_TABLE_FIELD_GROUP_ID = 'group_id';
    public const USERS_OFF_TABLE_FIELD_USER_ID  = 'user_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'users' => [
                'model'       => 'User',
                'foreign_key' => 'id',
            ],
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_CODENAME    => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_DESCRIPTION => [
                ['max_length', [':value', 255]],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get(self::TABLE_FIELD_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setCodename(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_CODENAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->get(self::TABLE_FIELD_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setDescription(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_DESCRIPTION, $value);

        return $this;
    }
}
