<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\Role;
use BetaKiller\Model\RoleInterface;

/**
 * Class RoleRepository
 *
 * @package BetaKiller\Repository
 * @method Role getOrmInstance()
 */
class RoleRepository extends AbstractOrmBasedMultipleParentsTreeRepository implements RoleRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return RoleInterface::URL_KEY;
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getGuestRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::GUEST);
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getLoginRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::LOGIN);
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getDeveloperRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::DEVELOPER);
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAdminPanelRole(): RoleInterface
    {
        return $this->getByName(RoleInterface::ADMIN_PANEL);
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByName(string $name): RoleInterface
    {
        $role = $this->findByName($name);

        if (!$role) {
            throw new RepositoryException('Can not find role by name :value', [
                ':value' => $name,
            ]);
        }

        return $role;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RoleInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByName(string $name): ?RoleInterface
    {
        $orm = $this->getOrmInstance();

        $this->filterName($orm, $name);

        return $this->findOne($orm);
    }

    /**
     * @inheritDoc
     */
    public function getChildParentsPairs(): array
    {
        $childCol = Role::getChildIdColumnName();
        $parentCol = Role::getParentIdColumnName();

        $data = \DB::select($childCol, $parentCol)
            ->from(Role::INHERITANCE_TABLE_NAME)
            ->execute()
            ->as_array();

        $pairs = [];

        foreach ($data as $row) {
            $childId = $row[$childCol];
            $parentId = $row[$parentCol];

            $pairs[$childId] = $pairs[$childId] ?? [];

            $pairs[$childId][] = $parentId;
        }

        return $pairs;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $name
     *
     * @return \BetaKiller\Repository\RoleRepository
     */
    private function filterName(ExtendedOrmInterface $orm, string $name): RoleRepository
    {
        $orm->where($orm->object_column('name'), '=', $name);

        return $this;
    }
}
