<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationException;

class Group extends \ORM implements GroupInterface
{
    public const TABLE_NAME              = 'notification_groups';
    public const TABLE_FIELD_CODENAME    = 'codename';
    public const TABLE_FIELD_DESCRIPTION = 'description';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->has_one([
            'notification_groups'     => [
                'model'       => 'User',
                'far_key'     => 'user_id',
                'through'     => 'notification_groups_users_off',
                'foreign_key' => 'group_id',
            ],
        ]);
        $this->has_many([
            'notification_groups_users_off'     => [
                'model'       => 'User',
                'far_key'     => 'user_id',
                'through'     => 'notification_groups_users_off',
                'foreign_key' => 'group_id',
            ],
        ]);

        $this->load_with(['notification_groups_users_off']);

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
     * @return \BetaKiller\Model\GroupInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setCodename(string $value): GroupInterface
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
     * @return \BetaKiller\Model\GroupInterface
     */
    public function setDescription(string $value): GroupInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_DESCRIPTION, $value);

        return $this;
    }
}
