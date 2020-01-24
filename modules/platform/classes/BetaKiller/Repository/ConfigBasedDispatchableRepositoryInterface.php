<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ConfigBasedDispatchableEntityInterface;

interface ConfigBasedDispatchableRepositoryInterface extends DispatchableRepositoryInterface
{
    /**
     * @param string $name
     *
     * @return ConfigBasedDispatchableEntityInterface|mixed
     */
    public function findByCodename(string $name);

    /**
     * @param string $name
     *
     * @return ConfigBasedDispatchableEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByCodename(string $name);
}
