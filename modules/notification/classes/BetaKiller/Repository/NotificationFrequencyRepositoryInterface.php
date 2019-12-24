<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationFrequencyInterface;

/**
 * Interface NotificationFrequencyRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method \BetaKiller\Model\NotificationFrequencyInterface[] getAll()
 */
interface NotificationFrequencyRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationFrequencyInterface
     */
    public function getByCodename(string $codename): NotificationFrequencyInterface;

    /**
     * @return NotificationFrequencyInterface[]
     */
    public function getScheduledFrequencies(): array;
}
