<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

/**
 * Class RoleRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method save(RoleInterface $model)
 * @method RoleInterface findById(int $id)
 * @method RoleInterface[] getAll()
 */
interface RoleRepositoryInterface extends MultipleParentsTreeRepositoryInterface, DispatchableRepositoryInterface
{
    /**
     * @return \BetaKiller\Model\RoleInterface
     */
    public function getGuestRole(): RoleInterface;

    /**
     * @return \BetaKiller\Model\RoleInterface
     */
    public function getLoginRole(): RoleInterface;

    /**
     * @return \BetaKiller\Model\RoleInterface
     */
    public function getDeveloperRole(): RoleInterface;

    /**
     * @return \BetaKiller\Model\RoleInterface
     */
    public function getAdminPanelRole(): RoleInterface;

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RoleInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByName(string $name): RoleInterface;

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RoleInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByName(string $name): ?RoleInterface;

    /**
     * Returns "child ID" => "array of parents` IDs" pairs
     *
     * @return string[][]
     */
    public function getChildParentsPairs(): array;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return RoleInterface[]
     */
    public function getAllUserRoles(UserInterface $user): array;
}
