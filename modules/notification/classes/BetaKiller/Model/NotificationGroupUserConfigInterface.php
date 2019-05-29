<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupUserConfigInterface extends AbstractEntityInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function bindToUser(UserInterface $user): NotificationGroupUserConfigInterface;

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function bindToGroup(NotificationGroupInterface $group): NotificationGroupUserConfigInterface;

    /**
     * @return bool
     */
    public function hasFrequencyDefined(): bool;

    /**
     * @return \BetaKiller\Model\NotificationFrequencyInterface
     */
    public function getFrequency(): NotificationFrequencyInterface;

    /**
     * @param \BetaKiller\Model\NotificationFrequencyInterface $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface
     */
    public function setFrequency(NotificationFrequencyInterface $value): NotificationGroupUserConfigInterface;
}
