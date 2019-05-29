<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use ORM;

class NotificationGroupUserConfig extends ORM implements NotificationGroupUserConfigInterface
{
    public const TABLE_NAME = 'notification_group_user_config';

    public const COL_USER_ID  = 'user_id';
    public const COL_GROUP_ID = 'group_id';
    public const COL_FREQ_ID  = 'freq_id';

    private const REL_USER  = 'user';
    private const REL_GROUP = 'group';
    private const REL_FREQ  = 'frequency';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            self::REL_USER => [
                'model'       => User::detectModelName(),
                'foreign_key' => self::COL_USER_ID,
            ],

            self::REL_GROUP => [
                'model'       => NotificationGroup::detectModelName(),
                'foreign_key' => self::COL_GROUP_ID,
            ],

            self::REL_FREQ => [
                'model'       => NotificationFrequency::detectModelName(),
                'foreign_key' => self::COL_FREQ_ID,
            ],
        ]);
    }

    public function rules(): array
    {
        return [
            self::COL_USER_ID => [
                ['not_empty'],
            ],

            self::COL_GROUP_ID => [
                ['not_empty'],
            ],

            self::COL_FREQ_ID => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function bindToUser(UserInterface $user): NotificationGroupUserConfigInterface
    {
        $this->set(self::REL_USER, $user);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function bindToGroup(NotificationGroupInterface $group): NotificationGroupUserConfigInterface
    {
        $this->set(self::REL_GROUP, $group);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasFrequencyDefined(): bool
    {
        return (bool)$this->get(self::COL_FREQ_ID);
    }

    /**
     * @return \BetaKiller\Model\NotificationFrequencyInterface
     */
    public function getFrequency(): NotificationFrequencyInterface
    {
        return $this->getRelatedEntity(self::REL_FREQ);
    }

    /**
     * @param \BetaKiller\Model\NotificationFrequencyInterface $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function setFrequency(NotificationFrequencyInterface $value): NotificationGroupUserConfigInterface
    {
        $this->set(self::REL_FREQ, $value);

        return $this;
    }
}
