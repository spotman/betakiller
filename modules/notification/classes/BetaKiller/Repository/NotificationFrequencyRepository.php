<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationFrequency;
use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class NotificationFrequencyRepository extends AbstractOrmBasedRepository implements
    NotificationFrequencyRepositoryInterface
{
    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationFrequencyInterface
     */
    public function getByCodename(string $codename): NotificationFrequencyInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCodename($orm, $codename)
            ->getOne($orm);
    }

    /**
     * @return NotificationFrequencyInterface[]
     */
    public function getScheduledFrequencies(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCodename($orm, NotificationFrequency::FREQ_NOW, true)
            ->findAll($orm);
    }

    private function filterCodename(OrmInterface $orm, string $codename, bool $not = null): self
    {
        $orm->where($orm->object_column(NotificationFrequency::COL_CODENAME), $not ? '<>' : '=', $codename);

        return $this;
    }
}
