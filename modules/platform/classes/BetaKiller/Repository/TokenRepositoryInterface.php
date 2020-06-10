<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;

/**
 * @method TokenInterface findById(string $id)
 * @method TokenInterface[] getAll()
 * @method void save(TokenInterface $entity)
 */
interface TokenRepositoryInterface extends DispatchableRepositoryInterface
{
    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByValue(string $value): ?TokenInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findActive(string $value): ?TokenInterface;

    /**
     * @return TokenInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findAllNotActive(): array;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return TokenInterface[]
     */
    public function getUserTokens(UserInterface $user): array;
}
