<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationException;

class NotificationGroup extends \ORM implements NotificationGroupInterface
{
    public const TABLE_NAME              = 'notification_groups';
    public const TABLE_FIELD_CODENAME    = 'codename';
    public const TABLE_FIELD_DESCRIPTION = 'description';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->has_many([
            'group_users'     => [
                'model'       => 'User',
                'far_key'     => 'user_id',
                'through'     => 'notification_groups_users_off',
                'foreign_key' => 'user_id',
            ],
        ]);

//        $this->load_with(['group_users']);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            'codename'    => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            'description' => [
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
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setCodename(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        if ($value === '') {
            throw new NotificationException('Codename cant not be empty');
        }
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
