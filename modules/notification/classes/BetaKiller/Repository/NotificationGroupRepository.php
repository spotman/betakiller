<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;

class NotificationGroupRepository extends AbstractOrmBasedDispatchableRepository implements
    NotificationGroupRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return NotificationGroup::COL_CODENAME;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findByCodename(string $codename): ?NotificationGroupInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codename)
            ->findOne($orm);
    }

    public function getByCodename(string $codename): NotificationGroupInterface
    {
        $group = $this->findByCodename($codename);

        if (!$group) {
            throw new RepositoryException('Group not found by group codename ":codename"', [
                ':codename' => $codename,
            ]);
        }

        return $group;
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getAllEnabled(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterGroupIsEnabled($orm, true)
            ->orderByName($orm)
            ->findAll($orm);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getAllDisabled(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterGroupIsEnabled($orm, false)
            ->orderByName($orm)
            ->findAll($orm);
    }

    /**
     * @return NotificationGroupInterface[]
     */
    public function getScheduledGroups(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterGroupIsEnabled($orm, true)
            ->filterScheduled($orm)
            ->orderByPlace($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @param bool|null                       $includeSystem
     *
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getUserGroups(UserInterface $user, bool $includeSystem = null): array
    {
        $orm = $this->getOrmInstance();

        if (!$includeSystem) {
            $this->filterSystemGroup($orm, false);
        }

        return $this
            ->filterGroupIsEnabled($orm, true)
            ->filterRoles($orm, $user->getAccessControlRoles())
            ->orderByPlace($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codename
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(NotificationGroup::COL_CODENAME), '=', $codename);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param bool                                   $value
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterGroupIsEnabled(ExtendedOrmInterface $orm, bool $value): self
    {
        $orm->where(
            $orm->object_column(NotificationGroup::COL_IS_ENABLED),
            '=',
            $value
        );

        return $this;
    }

    private function filterSystemGroup(ExtendedOrmInterface $orm, bool $value): self
    {
        $orm->where(
            $orm->object_column(NotificationGroup::COL_IS_SYSTEM),
            '=',
            $value
        );

        return $this;
    }

    private function filterScheduled(ExtendedOrmInterface $orm): self
    {
        $orm->where(
            $orm->object_column(NotificationGroup::COL_IS_FREQ_CONTROLLED),
            '=',
            true
        );

        return $this;
    }

    private function orderByName(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column(NotificationGroup::COL_CODENAME), 'asc');

        return $this;
    }

    private function orderByPlace(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column(NotificationGroup::COL_PLACE), 'asc');

        return $this;
    }

    private function filterRoles(ExtendedOrmInterface $orm, array $roles): self
    {
        $this->filterRelatedMultiple($orm, NotificationGroup::RELATION_ROLES, $roles);

        return $this;
    }
}
